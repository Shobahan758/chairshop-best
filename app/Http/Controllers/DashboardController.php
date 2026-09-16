<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Product;
use App\Services\CustomerAccounts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CustomerAccounts $accounts): View|RedirectResponse
    {
        if ($request->user()?->is_admin && ! $accounts->current($request)) {
            return redirect()->route('admin');
        }
        if (! $request->user() && ! $accounts->current($request) && ! $request->session()->get('customer_order_ids', [])) {
            return redirect()->guest(route('login'));
        }
        $section = $request->query('section', 'orders');
        abort_unless(in_array($section, ['profile', 'orders', 'wishlist', 'wallet', 'loyalty', 'inbox', 'addresses', 'support', 'referrals', 'coupons', 'track'], true), 404);
        $account = $accounts->resolve($request);
        $orders = $accounts->orders($request, $account)->with('items')->latest()->get();
        $selectedOrder = null;
        $orderTab = $request->query('tab', 'track');
        if ($request->filled('order')) {
            abort_unless($section === 'orders' && in_array($orderTab, ['summary', 'vendor', 'delivery', 'reviews', 'track'], true), 404);
            $selectedOrder = $orders->firstWhere('id', $request->integer('order'));
            abort_unless($selectedOrder, 404);
        }
        $tickets = $account->tickets()->latest('updated_at')->get();
        $messages = collect();
        foreach ($orders as $order) {
            $messages->push(['title' => $order->order_number, 'body' => $order->status, 'at' => $order->updated_at, 'url' => route('dashboard', ['section' => 'orders', 'order' => $order->id, 'tab' => 'track'])]);
        }
        foreach ($tickets as $ticket) {
            foreach ($ticket->messages as $message) {
                if ($message['author'] === 'support') {
                    $messages->push(['title' => $ticket->subject, 'body' => $message['body'], 'at' => Carbon::parse($message['at']), 'url' => route('dashboard', ['section' => 'support']).'#ticket-'.$ticket->id]);
                }
            }
        }

        return view('dashboard', [
            'selectedOrder' => $selectedOrder, 'orderTab' => $orderTab,
            'store' => GeneralSetting::first() ?? new GeneralSetting,
            'orderProducts' => $selectedOrder ? Product::whereIn('id', $selectedOrder->items->pluck('product_id'))->where('is_active', true)->get()->keyBy('id') : collect(),
            'section' => $section, 'account' => $account, 'orders' => $orders, 'tickets' => $tickets,
            'messages' => $messages->sortByDesc('at'),
            'wishlist' => $section === 'wishlist' ? Product::query()->with('category')->where('is_active', true)->whereIn('id', $account->wishlist ?? [])->get() : collect(),
            'coupons' => $section === 'coupons' ? Coupon::query()->where('is_active', true)->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->get() : collect(),
            'referralOrders' => $section === 'referrals' ? Order::query()->where('referrer_account_id', $account->id)->where('is_fake', false)->count() : 0,
        ]);
    }

    public function invoice(Request $request, Order $order, CustomerAccounts $accounts): View
    {
        abort_if($request->user()?->is_admin && ! $accounts->current($request), 403);
        abort_unless($request->user() || $accounts->current($request) || $request->session()->get('customer_order_ids', []), 404);
        $account = $accounts->resolve($request);
        $ownedOrder = $accounts->orders($request, $account)->with('items')->findOrFail($order->id);

        return view('order-invoice', ['order' => $ownedOrder, 'store' => GeneralSetting::first() ?? new GeneralSetting]);
    }
}
