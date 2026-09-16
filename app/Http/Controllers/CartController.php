<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Services\StorefrontTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = session('cart', []);
        $subtotal = (float) collect($cart)->sum(fn (array $item): float => (float) $item['price'] * (int) $item['quantity']);
        $coupon = Coupon::query()->where('code', session('coupon_code'))->first();

        if ($coupon && ! $coupon->isValidFor($subtotal)) {
            session()->forget('coupon_code');
            $coupon = null;
        }

        return view('cart', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $coupon?->discountFor($subtotal) ?? 0,
        ]);
    }

    public function add(Request $request, Product $product, StorefrontTracker $tracker): RedirectResponse
    {
        abort_if(! $product->is_active || $product->stock < 1, 422, 'পণ্যটি পাওয়া যাচ্ছে না');
        $cart = session('cart', []);
        $previousQuantity = $cart[$product->id]['quantity'] ?? 0;
        $quantity = max(1, min((int) $request->input('quantity', 1), $product->stock));
        $cart[$product->id] = ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'image' => $product->image, 'price' => (float) $product->current_price, 'quantity' => min(($cart[$product->id]['quantity'] ?? 0) + $quantity, $product->stock)];
        session(['cart' => $cart]);
        $tracker->record($request, 'add_to_cart', ['product_id' => $product->id, 'quantity' => $cart[$product->id]['quantity'] - $previousQuantity, 'value' => ($cart[$product->id]['quantity'] - $previousQuantity) * (float) $product->current_price]);

        return back()->with('success', 'পণ্যটি কার্টে যোগ হয়েছে');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_if(! $product->is_active || $product->stock < 1, 422, 'পণ্যটি পাওয়া যাচ্ছে না');
        $cart = session('cart', []);
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] = max(1, min($request->integer('quantity'), $product->stock));
            session(['cart' => $cart]);
        }

        return back()->with('success', 'কার্ট আপডেট হয়েছে');
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'পণ্যটি সরানো হয়েছে');
    }
}
