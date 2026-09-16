<?php

namespace App\Services;

use App\Models\CustomerAccount;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerAccounts
{
    public function current(Request $request): ?CustomerAccount
    {
        if ($request->user() && ! $request->user()->is_admin) {
            return CustomerAccount::query()->where('user_id', $request->user()->id)->first();
        }

        return CustomerAccount::query()->whereNull('user_id')->find($request->session()->get('customer_account_id'));
    }

    public function resolve(Request $request): CustomerAccount
    {
        $user = $request->user()?->is_admin ? null : $request->user();
        $account = $this->current($request);

        if (! $account) {
            $order = Order::query()->whereIn('id', $request->session()->get('customer_order_ids', []))->latest()->first();
            $attributes = [
                'name' => $user?->name ?? $order?->name,
                'email' => $user?->email ?? $order?->email,
                'phone' => $user?->phone ?? $order?->phone,
                'referral_code' => Str::lower(Str::random(16)),
            ];
            $account = $user
                ? CustomerAccount::query()->firstOrCreate(['user_id' => $request->user()->id], $attributes)
                : CustomerAccount::query()->create($attributes);
        }

        if (! $user) {
            $request->session()->put('customer_account_id', $account->id);
        }

        return $account;
    }

    public function orders(Request $request, CustomerAccount $account): Builder
    {
        return Order::query()->where('is_fake', false)->where(function (Builder $query) use ($request, $account) {
            $query->where('customer_account_id', $account->id)->orWhere(function (Builder $legacy) use ($request) {
                $legacy->whereNull('customer_account_id');
                if ($request->user() && ! $request->user()->is_admin) {
                    $legacy->where('email', $request->user()->email);
                } else {
                    $legacy->whereIn('id', $request->session()->get('customer_order_ids', []));
                }
            });
        });
    }
}
