@extends('layouts.admin')
@section('title', 'Business Overview — ChairGhor')
@section('content')
<div class="admin-page-heading">
    <div><span>{{ now()->format('l, d F Y') }}</span><h1>Business Overview</h1><p>Orders, customers, inventory and website activity in one place.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('home') }}" class="btn btn-admin-light">View Store</a>@if(auth()->user()->canAccessAdminRoute('admin.products.create'))<a href="{{ route('admin.products.create') }}" class="btn btn-admin-primary">Add Product</a>@endif</div>
</div>
<section class="admin-panel p-3 mb-4">
    <form method="get" action="{{ route('admin') }}" class="d-flex gap-3 flex-wrap align-items-end">
        <div><label for="reportFrom" class="form-label">From</label><input id="reportFrom" type="date" class="form-control" name="from" value="{{ old('from', $start->toDateString()) }}" required></div>
        <div><label for="reportTo" class="form-label">To</label><input id="reportTo" type="date" class="form-control" name="to" value="{{ old('to', $end->toDateString()) }}" required></div>
        <button class="btn btn-admin-primary" type="submit">Apply filter</button>
        <a class="btn btn-admin-light" href="{{ route('admin', ['from' => now()->toDateString(), 'to' => now()->toDateString()]) }}">Today</a>
        <a class="btn btn-admin-light" href="{{ route('admin', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}">This month</a>
        <a class="btn btn-admin-light" href="{{ route('admin') }}">Last 30 days</a>
    </form>
    @if($errors->any())<div class="alert alert-danger mt-3" role="alert">{{ $errors->first() }}</div>@endif
    <p class="text-muted small mb-0 mt-3">Period: {{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}. Orders are grouped by date placed and their current status. Fake orders are excluded from financial totals.</p>
</section>
<div class="row g-3">
    @foreach([
        ['Order value', '৳'.number_format($orderValue, 2), 'Excludes cancelled & fake'],
        ['Delivered order value', '৳'.number_format($deliveredValue, 2), 'Delivered orders placed in this period'],
        ['Pending order value', '৳'.number_format($pendingValue, 2), 'Not yet delivered'],
        ['Total orders', number_format($orderCount), 'Includes cancelled; excludes fake'],
        ['Unique buyers', number_format($buyers), 'Distinct order phone numbers'],
        ['New customer accounts', number_format($newCustomers), 'Created in this period'],
        ['Incomplete orders', number_format($incompleteCount), 'Remaining checkout drafts'],
        ['Fake orders', number_format($fakeCount), 'Flagged or marked fake'],
    ] as [$label, $value, $hint])
        <div class="col-sm-6 col-xl-3"><article class="admin-panel p-4 h-100"><div class="text-muted small">{{ $label }}</div><strong class="fs-3 d-block my-2">{{ $value }}</strong><small class="text-muted">{{ $hint }}</small></article></div>
    @endforeach
</div>
<div class="row g-4 mt-1">
    <div class="col-xl-8"><section class="admin-panel h-100">
        <div class="admin-panel-head"><div><span class="panel-kicker">DAILY TREND</span><h2>Order value over time</h2></div></div>
        @php($peak = max(1, collect($series)->max('value')))
        <div class="px-4 pb-4">
            <div class="overflow-auto">
                <svg viewBox="0 0 {{ max(660, count($series) * 22) }} 235" role="img" aria-label="Daily order value chart" style="width:100%;min-width:660px;height:235px">
                    @foreach($series as $point)
                        @php($step = max(660, count($series) * 22) / count($series))
                        @php($height = $point['value'] / $peak * 175)
                        <rect x="{{ $loop->index * $step + 3 }}" y="{{ 190 - $height }}" width="{{ max(2, $step - 6) }}" height="{{ $height }}" rx="3" fill="#245c4b"><title>{{ $point['day'] }}: ৳{{ number_format($point['value'], 2) }} · {{ $point['orders'] }} orders</title></rect>
                        @if($loop->first || $loop->last || $loop->index % max(1, (int) ceil(count($series) / 7)) === 0)
                        <text x="{{ $loop->index * $step + 3 }}" y="215" font-size="10" fill="#64748b">{{ substr($point['day'], 5) }}</text>
                        @endif
                    @endforeach
                    <line x1="0" y1="191" x2="{{ max(660, count($series) * 22) }}" y2="191" stroke="#dce5e1"/>
                </svg>
            </div>
            <p class="small text-muted">Daily peak: ৳{{ number_format(collect($series)->max('value'), 2) }}. Hover over a bar for its value. Cancelled and fake orders excluded.</p>
            <details><summary>View daily figures</summary><div class="table-responsive" style="max-height:260px"><table class="table table-sm"><thead><tr><th>Date</th><th>Orders</th><th>Order value</th></tr></thead><tbody>@foreach($series as $point)<tr><td>{{ $point['day'] }}</td><td>{{ $point['orders'] }}</td><td>৳{{ number_format($point['value'], 2) }}</td></tr>@endforeach</tbody></table></div></details>
        </div>
    </section></div>
    <div class="col-xl-4"><section class="admin-panel h-100">
        <div class="admin-panel-head"><div><span class="panel-kicker">ORDER ANALYTICS</span><h2>Current order status</h2></div></div>
        <div class="px-4 pb-4">
            @foreach(['অর্ডার গ্রহণ' => 'New', 'নিশ্চিত' => 'Confirmed', 'প্রস্তুত' => 'Preparing', 'পাঠানো' => 'Shipping', 'ডেলিভারি সম্পন্ন' => 'Delivered', 'বাতিল' => 'Cancelled'] as $status => $label)
                @php($count = (int) $statusCounts->get($status, 0))
                <div class="d-flex justify-content-between mt-3"><span>{{ $label }}</span><strong>{{ number_format($count) }}</strong></div>
                <div class="progress mt-1" style="height:6px"><div class="progress-bar bg-success" style="width:{{ $orderCount ? $count / $orderCount * 100 : 0 }}%"></div></div>
            @endforeach
        </div>
    </section></div>
</div>
<div class="row g-4 mt-1">
    <div class="col-lg-6"><section class="admin-panel h-100">
        <div class="admin-panel-head"><h2>Financial breakdown</h2></div>
        <div class="px-4 pb-4">
            @foreach(['Product subtotal' => $subtotalValue, 'Discounts' => $discountValue, 'Delivery charges' => $deliveryValue, 'Order value' => $orderValue, 'Cancelled value (excluded)' => $cancelledValue] as $label => $value)
                <div class="d-flex justify-content-between border-bottom py-2"><span>{{ $label }}</span><strong>৳{{ number_format($value, 2) }}</strong></div>
            @endforeach
            <p class="small text-muted mt-3 mb-0">Order value = product subtotal − discounts + delivery charges. These are order values, not confirmed cash receipts or profit.</p>
        </div>
    </section></div>
    <div class="col-lg-6"><section class="admin-panel h-100">
        <div class="admin-panel-head"><h2>Payment methods</h2></div>
        <div class="table-responsive px-4 pb-4"><table class="table"><thead><tr><th>Method</th><th>Orders</th><th>Order value</th></tr></thead><tbody>@forelse($paymentTotals as $payment)<tr><td>{{ strtoupper($payment->payment_method) }}</td><td>{{ (int) $payment->orders }}</td><td>৳{{ number_format($payment->value, 2) }}</td></tr>@empty<tr><td colspan="3">No orders in this period.</td></tr>@endforelse</tbody></table></div>
    </section></div>
</div>
<div class="row g-4 mt-1">
    <div class="col-lg-6"><section class="admin-panel h-100"><div class="admin-panel-head"><h2>Top products</h2></div>
        <div class="table-responsive px-4 pb-4"><table class="table"><thead><tr><th>Product</th><th>Units ordered</th><th>Item value</th></tr></thead><tbody>@forelse($topProducts as $product)<tr><td>{{ $product->name }}</td><td>{{ (int) $product->units }}</td><td>৳{{ number_format($product->value, 2) }}</td></tr>@empty<tr><td colspan="3">No product sales in this period.</td></tr>@endforelse</tbody></table><small class="text-muted">Item value before order discounts and delivery. Excludes cancelled and fake orders.</small></div>
    </section></div>
    <div class="col-lg-6"><section class="admin-panel h-100"><div class="admin-panel-head"><h2>Website activity</h2></div>
        <div class="px-4 pb-4">
            @foreach(['Tracked sessions' => $trackedSessions, 'Page views' => $eventCounts->get('page_view', 0), 'Add to cart events' => $eventCounts->get('add_to_cart', 0), 'Checkout events' => $eventCounts->get('checkout_open', 0), 'Purchase events' => $eventCounts->get('purchase_complete', 0)] as $label => $count)
                <div class="d-flex justify-content-between border-bottom py-2"><span>{{ $label }}</span><strong>{{ number_format((int) $count) }}</strong></div>
            @endforeach
            <p class="small text-muted mt-3">Recorded events only; browser blocking or disabled tracking can reduce counts. Sessions are not unique people.</p>
        </div>
    </section></div>
</div>
<section class="admin-panel mt-4">
    <div class="admin-panel-head"><h2>Recent orders</h2>@if(auth()->user()->canAccessAdminRoute('admin.orders.index'))<a href="{{ route('admin.orders.index') }}">All orders →</a>@endif</div>
    <div class="table-responsive"><table class="table admin-orders-table"><thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Value</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($orders as $order)<tr><td>{{ $order->order_number }}</td><td>{{ $order->name }}<small class="d-block text-muted">{{ $order->phone }}</small></td><td>{{ $order->created_at->format('d M Y') }}</td><td>৳{{ number_format($order->total, 2) }}</td><td>{{ $order->status }}</td><td>@if(auth()->user()->canAccessAdminRoute('admin.orders.edit'))<a class="btn btn-admin-light btn-sm" href="{{ route('admin.orders.edit', $order) }}">View / Edit</a>@endif</td></tr>
        @empty<tr><td colspan="6" class="text-center py-4">No orders in this period.</td></tr>@endforelse
    </tbody></table></div>
</section>
<section class="admin-panel mt-4">
    <div class="admin-panel-head"><div><span class="panel-kicker">CURRENT SNAPSHOT · NOT DATE FILTERED</span><h2>Inventory & support</h2></div></div>
    <div class="row g-3 px-4 pb-4">
        @foreach(['Products' => $productCount, 'Active products' => $activeProducts, 'Stock units' => $stockUnits, 'Low stock (1–9)' => $lowStockCount, 'Out of stock' => $outOfStock, 'Open support tickets' => $openTickets] as $label => $value)
            <div class="col-6 col-lg-2"><small class="text-muted">{{ $label }}</small><strong class="d-block fs-4">{{ number_format($value) }}</strong></div>
        @endforeach
    </div>
    <div class="table-responsive px-4 pb-4"><table class="table"><thead><tr><th>Stock alerts</th><th>Category</th><th>SKU</th><th>Available</th></tr></thead><tbody>
        @forelse($lowStockProducts as $product)<tr><td>@if(auth()->user()->canAccessAdminRoute('admin.products.edit'))<a href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>@else{{ $product->name }}@endif</td><td>{{ $product->category?->name ?? '—' }}</td><td>{{ $product->sku }}</td><td><span class="badge bg-danger">{{ $product->stock }}</span></td></tr>@empty<tr><td colspan="4">No stock alerts.</td></tr>@endforelse
    </tbody></table></div>
</section>
<section class="admin-panel mt-4"><div class="admin-panel-head"><h2>Quick access</h2></div><div class="d-flex flex-wrap gap-2 px-4 pb-4">
    @foreach(['admin.products.index' => 'Products', 'admin.categories.index' => 'Categories', 'admin.incomplete-orders.index' => 'Incomplete Orders', 'admin.fake-orders.index' => 'Fake Orders', 'admin.support.index' => 'Messages', 'admin.tracking' => 'Tracking', 'admin.settings.site' => 'Site Settings', 'admin.settings.general' => 'General Settings', 'admin.settings.users' => 'Role'] as $route => $label)
        @if(auth()->user()->canAccessAdminRoute($route))<a class="btn btn-admin-light" href="{{ route($route) }}">{{ $label }}</a>@endif
    @endforeach
</div></section>
@endsection
