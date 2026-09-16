<?php

namespace App\Services;

use App\Models\CustomerAccount;
use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\TrackingEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardReport
{
    /** @return array<string, mixed> */
    public function build(Carbon $start, Carbon $end): array
    {
        $orders = Order::query()->whereBetween('created_at', [$start, $end]);
        $valid = (clone $orders)->where('is_fake', false);
        $active = (clone $valid)->where('status', '!=', 'বাতিল');
        $statusCounts = (clone $valid)->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $totals = (clone $active)->selectRaw('COALESCE(SUM(total), 0) as order_value, COALESCE(SUM(subtotal), 0) as subtotal_value, COALESCE(SUM(discount), 0) as discount_value, COALESCE(SUM(delivery_charge), 0) as delivery_value')->first();
        $daily = (clone $valid)->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(CASE WHEN status != ? THEN total ELSE 0 END) as value', ['বাতিল'])->groupByRaw('DATE(created_at)')->get()->keyBy('day');
        $series = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $row = $daily->get($day->toDateString());
            $series[] = ['day' => $day->toDateString(), 'orders' => (int) ($row?->orders ?? 0), 'value' => (float) ($row?->value ?? 0)];
        }
        $events = TrackingEvent::whereBetween('created_at', [$start, $end]);
        $topProducts = DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$start, $end])->where('orders.is_fake', false)->where('orders.status', '!=', 'বাতিল')
            ->selectRaw('order_items.product_id, MAX(order_items.product_name) as name, SUM(order_items.quantity) as units, SUM(order_items.quantity * order_items.price) as value')
            ->groupBy('order_items.product_id')->orderByDesc('units')->limit(5)->get();

        return [
            'start' => $start, 'end' => $end, 'series' => $series,
            'orderCount' => (clone $valid)->count(),
            'orderValue' => (float) $totals->order_value,
            'subtotalValue' => (float) $totals->subtotal_value,
            'discountValue' => (float) $totals->discount_value,
            'deliveryValue' => (float) $totals->delivery_value,
            'deliveredValue' => (float) (clone $valid)->where('status', 'ডেলিভারি সম্পন্ন')->sum('total'),
            'pendingValue' => (float) (clone $active)->where('status', '!=', 'ডেলিভারি সম্পন্ন')->sum('total'),
            'cancelledValue' => (float) (clone $valid)->where('status', 'বাতিল')->sum('total'),
            'buyers' => (clone $valid)->distinct()->count('phone'),
            'fakeCount' => (clone $orders)->where('is_fake', true)->count(),
            'incompleteCount' => IncompleteOrder::whereBetween('created_at', [$start, $end])->count(),
            'newCustomers' => CustomerAccount::whereBetween('created_at', [$start, $end])->count(),
            'statusCounts' => $statusCounts,
            'orders' => (clone $valid)->latest()->limit(8)->get(),
            'topProducts' => $topProducts,
            'paymentTotals' => (clone $active)->selectRaw('payment_method, COUNT(*) as orders, SUM(total) as value')->groupBy('payment_method')->get(),
            'eventCounts' => (clone $events)->selectRaw('event, COUNT(*) as aggregate')->groupBy('event')->pluck('aggregate', 'event'),
            'trackedSessions' => (clone $events)->distinct()->count('session_hash'),
            'productCount' => Product::count(), 'activeProducts' => Product::where('is_active', true)->count(),
            'stockUnits' => (int) Product::sum('stock'), 'outOfStock' => Product::where('stock', '<=', 0)->count(),
            'lowStockCount' => Product::whereBetween('stock', [1, 9])->count(),
            'lowStockProducts' => Product::with('category')->where('stock', '<', 10)->orderBy('stock')->limit(8)->get(),
            'openTickets' => SupportTicket::where('status', 'open')->count(),
        ];
    }
}
