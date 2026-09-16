<?php

namespace App\Http\Controllers;

use App\Models\CustomerAccount;
use App\Models\Product;
use App\Services\CustomerAccounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerAccountController extends Controller
{
    public function profile(Request $request, CustomerAccounts $accounts): RedirectResponse
    {
        $account = $accounts->resolve($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
        if ($request->user() && ! $request->user()->is_admin) {
            $data['email'] = $request->user()->email;
            $request->user()->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);
        }
        $account->update($data);

        return back()->with('success', 'প্রোফাইল সংরক্ষণ হয়েছে।');
    }

    public function password(Request $request): RedirectResponse
    {
        abort_unless($request->user() && ! $request->user()->is_admin, 403);
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'পাসওয়ার্ড পরিবর্তন হয়েছে।');
    }

    public function wishlist(Request $request, Product $product, CustomerAccounts $accounts): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $account = $accounts->resolve($request);
        DB::transaction(function () use ($account, $product, $request): void {
            $locked = CustomerAccount::query()->lockForUpdate()->findOrFail($account->id);
            $ids = collect($locked->wishlist ?? []);
            $ids = $request->isMethod('delete') ? $ids->reject(fn ($id) => (int) $id === $product->id) : $ids->push($product->id)->unique();
            $locked->update(['wishlist' => $ids->values()->all()]);
        });

        return back()->with('success', $request->isMethod('delete') ? 'উইশলিস্ট থেকে সরানো হয়েছে।' : 'উইশলিস্টে যোগ হয়েছে।');
    }

    public function address(Request $request, CustomerAccounts $accounts): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'uuid'], 'label' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:100'], 'phone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'district' => ['required', 'string', 'max:100'], 'area' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $account = $accounts->resolve($request);
        DB::transaction(function () use ($account, $data): void {
            $locked = CustomerAccount::query()->lockForUpdate()->findOrFail($account->id);
            $addresses = collect($locked->addresses ?? []);
            if (! empty($data['id'])) {
                abort_unless($addresses->contains('id', $data['id']), 404);
                $addresses = $addresses->reject(fn ($address) => $address['id'] === $data['id']);
            } else {
                abort_if($addresses->count() >= 20, 422, 'সর্বোচ্চ ২০টি ঠিকানা রাখা যাবে।');
            }
            $addresses->push([...$data, 'id' => $data['id'] ?? (string) Str::uuid()]);
            $locked->update(['addresses' => $addresses->values()->all()]);
        });

        return redirect()->route('dashboard', ['section' => 'addresses'])->with('success', 'ঠিকানা সংরক্ষণ হয়েছে।');
    }

    public function deleteAddress(Request $request, string $address, CustomerAccounts $accounts): RedirectResponse
    {
        $account = $accounts->resolve($request);
        DB::transaction(function () use ($account, $address): void {
            $locked = CustomerAccount::query()->lockForUpdate()->findOrFail($account->id);
            $addresses = collect($locked->addresses ?? []);
            abort_unless($addresses->contains('id', $address), 404);
            $locked->update(['addresses' => $addresses->reject(fn ($row) => $row['id'] === $address)->values()->all()]);
        });

        return back()->with('success', 'ঠিকানা সরানো হয়েছে।');
    }

    public function ticket(Request $request, CustomerAccounts $accounts): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'phone' => ['sometimes', 'required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ]);
        $isAdmin = $request->user()?->is_admin === true && ! $accounts->current($request);
        $account = $isAdmin
            ? CustomerAccount::firstOrCreate(['user_id' => $request->user()->id], [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone' => $request->user()->phone,
                'referral_code' => Str::lower(Str::random(16)),
            ])
            : $accounts->resolve($request);
        $account->tickets()->create([
            'subject' => $data['subject'], 'status' => 'open',
            'messages' => [[
                'author' => 'customer', 'body' => $data['message'], 'at' => now()->toIso8601String(),
                'contact' => array_intersect_key($data, array_flip(['name', 'phone', 'email'])),
            ]],
        ]);

        if ($isAdmin) {
            return redirect()->route('contact')->with('success', 'আপনার মেসেজ পাঠানো হয়েছে।');
        }

        return redirect()->route('dashboard', ['section' => 'support'])->with('success', 'সাপোর্ট টিকিট পাঠানো হয়েছে।');
    }

    public function reply(Request $request, int $ticket, CustomerAccounts $accounts): RedirectResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        $account = $accounts->resolve($request);
        DB::transaction(function () use ($account, $ticket, $data): void {
            $record = $account->tickets()->lockForUpdate()->findOrFail($ticket);
            $record->update(['status' => 'open', 'messages' => [...$record->messages, ['author' => 'customer', 'body' => $data['message'], 'at' => now()->toIso8601String()]]]);
        });

        return back()->with('success', 'মেসেজ পাঠানো হয়েছে।');
    }

    public function readInbox(Request $request, CustomerAccounts $accounts): RedirectResponse
    {
        $accounts->resolve($request)->update(['inbox_read_at' => now()]);

        return back()->with('success', 'সব মেসেজ পড়া হয়েছে।');
    }

    public function referral(Request $request, string $code, CustomerAccounts $accounts): RedirectResponse
    {
        $referrer = CustomerAccount::query()->where('referral_code', $code)->firstOrFail();
        if ($accounts->current($request)?->id !== $referrer->id && ! $request->session()->has('referrer_account_id')) {
            $request->session()->put('referrer_account_id', $referrer->id);
            $referrer->increment('referral_visits');
        }

        return redirect()->route('shop');
    }
}
