<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\StoreIncompleteOrderRequest;
use App\Models\Coupon;
use App\Models\CustomerAccount;
use App\Models\GeneralSetting;
use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\Product;
use App\Services\CustomerAccounts;
use App\Services\OrderRiskScorer;
use App\Services\StorefrontTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function saveIncomplete(StoreIncompleteOrderRequest $request): JsonResponse
    {
        $cart = session('cart', []);
        abort_if(empty($cart), 422, 'কার্ট খালি');

        IncompleteOrder::query()->updateOrCreate(
            ['session_id' => $request->session()->getId()],
            [...$request->validated(), 'quantity' => collect($cart)->sum('quantity'), 'cart' => array_values($cart)],
        );

        return response()->json(['saved' => true]);
    }

    public function index(Request $request, CustomerAccounts $accounts): View|RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart')->with('success', 'চেকআউটের আগে কার্টে পণ্য যোগ করুন');
        }

        $subtotal = $this->cartSubtotal($cart);
        $coupon = $this->activeCoupon($subtotal);

        return view('checkout', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $coupon?->discountFor($subtotal) ?? 0,
            'paymentSettings' => GeneralSetting::first() ?? new GeneralSetting,
            'savedAddresses' => $accounts->current($request)?->addresses ?? [],
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate(['coupon_code' => ['required', 'string', 'max:50']]);
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart');
        }

        $coupon = Coupon::query()->where('code', strtoupper(trim($validated['coupon_code'])))->first();

        if (! $coupon || ! $coupon->isValidFor($this->cartSubtotal($cart))) {
            return back()->withErrors(['coupon_code' => 'কুপন কোডটি সঠিক নয় অথবা এই অর্ডারে প্রযোজ্য নয়।']);
        }

        session(['coupon_code' => $coupon->code]);

        return back()->with('success', 'কুপন সফলভাবে প্রয়োগ হয়েছে।');
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('coupon_code');

        return back()->with('success', 'কুপন সরানো হয়েছে।');
    }

    public function store(CheckoutRequest $request, StorefrontTracker $tracker, CustomerAccounts $accounts, OrderRiskScorer $riskScorer): RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            if (($request->user() && ! $request->user()->is_admin) || $request->session()->get('customer_order_ids', [])) {
                return redirect()->route('dashboard')->with('success', 'কার্ট খালি। আপনার অর্ডারের তথ্য এখানে দেখুন।');
            }

            return redirect()->route('cart')->with('success', 'চেকআউটের আগে কার্টে পণ্য যোগ করুন');
        }

        $account = $accounts->resolve($request);
        $order = Cache::lock('checkout-order-risk', 30)->block(10, fn (): Order => DB::transaction(function () use ($request, $cart, $account, $riskScorer): Order {
            $products = Product::query()->where('is_active', true)->whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0.0;

            foreach ($cart as $row) {
                $product = $products->get($row['id']);
                abort_if(! $product || $product->stock < $row['quantity'], 422, ($product?->name ?? 'পণ্য').' পর্যাপ্ত স্টকে নেই');
                $subtotal += (float) $product->current_price * $row['quantity'];
            }

            $coupon = $this->activeCoupon($subtotal);
            $discount = $coupon?->discountFor($subtotal) ?? 0;
            $district = mb_strtolower(trim($request->string('district')->toString()));
            $delivery = in_array($district, config('delivery.dhaka_names'), true) ? config('delivery.dhaka') : config('delivery.outside_dhaka');
            $customerEmail = $request->user() && ! $request->user()->is_admin
                ? $request->user()->email
                : $request->validated('email');
            $risk = $riskScorer->assess(
                $request->validated('phone'), $request->validated('name'), $request->validated('address'),
                $request->validated('area'), $request->validated('district'), $request->ip(),
            );
            $order = Order::create([
                ...$risk,
                'source_ip' => $request->ip(),
                ...$request->validated(),
                'email' => $customerEmail,
                'order_number' => 'CG-'.now()->format('ymd').'-'.strtoupper(str()->random(5)),
                'subtotal' => $subtotal,
                'coupon_code' => $coupon?->code,
                'discount' => $discount,
                'delivery_charge' => $delivery,
                'total' => $subtotal - $discount + $delivery,
            ]);

            foreach ($cart as $row) {
                $product = $products->get($row['id']);
                $price = (float) $product->current_price;
                $product->decrement('stock', $row['quantity']);
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $row['quantity'], 'price' => $price]);
            }

            if ($account) {
                $order->customer_account_id = $account->id;
                $referrer = CustomerAccount::query()->find($request->session()->get('referrer_account_id'));
                if ($referrer && $referrer->id !== $account->id) {
                    $order->referrer_account_id = $referrer->id;
                }
                $order->save();
                if (! $account->name) {
                    $account->update(['name' => $order->name, 'email' => $order->email, 'phone' => $order->phone]);
                }
            }

            return $order;
        }));

        IncompleteOrder::query()->where('session_id', $request->session()->getId())->delete();
        session()->forget(['cart', 'coupon_code']);
        $tracker->record($request, 'purchase_complete', ['order_number' => $order->order_number, 'total' => (float) $order->total]);

        if (! $request->user() || $request->user()->is_admin) {
            $request->session()->push('customer_order_ids', $order->id);
        }

        return redirect()->route('dashboard')->with('success', 'আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে।');
    }

    public function success(Order $order): View
    {
        return view('success', compact('order'));
    }

    /** @param array<int|string, array<string, mixed>> $cart */
    private function cartSubtotal(array $cart): float
    {
        return (float) collect($cart)->sum(fn (array $item): float => (float) $item['price'] * (int) $item['quantity']);
    }

    private function activeCoupon(float $subtotal): ?Coupon
    {
        $code = session('coupon_code');

        if (! $code) {
            return null;
        }

        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon || ! $coupon->isValidFor($subtotal)) {
            session()->forget('coupon_code');

            return null;
        }

        return $coupon;
    }
}
