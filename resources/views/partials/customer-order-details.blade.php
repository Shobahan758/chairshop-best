@php
    $detailText = fn (string $key): string => $siteContent->text('account', 'order_details', $key);
    $stages = ['অর্ডার গ্রহণ' => ['placed', 'clipboard-check'], 'নিশ্চিত' => ['confirmed', 'receipt'], 'প্রস্তুত' => ['preparing', 'box-seam'], 'পাঠানো' => ['on_way', 'truck'], 'ডেলিভারি সম্পন্ন' => ['delivered', 'geo-alt']];
    $stageIndex = array_search($selectedOrder->status, array_keys($stages), true);
    $history = collect($selectedOrder->status_history ?? []);
@endphp
<div class="customer-order-heading">
    <div><h3>{{ $detailText('order') }} #{{ $selectedOrder->order_number }} <span class="customer-status">{{ $selectedOrder->status === 'অর্ডার গ্রহণ' ? $detailText('pending') : $selectedOrder->status }}</span></h3><p>{{ $selectedOrder->created_at->format('d M, Y h:i A') }}</p></div>
    <div class="customer-order-actions"><a class="customer-back-button" href="{{ route('dashboard', ['section' => 'orders']) }}">{{ $detailText('back') }}</a><a class="customer-icon-button" href="{{ route('customer.orders.invoice', $selectedOrder) }}" target="_blank" rel="noopener" aria-label="{{ $detailText('invoice') }}" title="{{ $detailText('invoice') }}"><i class="bi bi-download"></i></a></div>
</div>
<nav class="customer-order-tabs" aria-label="{{ $detailText('order') }}">
    @foreach(['summary', 'vendor', 'delivery', 'reviews', 'track'] as $tab)
        <a href="{{ route('dashboard', ['section' => 'orders', 'order' => $selectedOrder->id, 'tab' => $tab]) }}" class="{{ $orderTab === $tab ? 'active' : '' }}" @if($orderTab === $tab) aria-current="page" @endif>{{ $detailText($tab) }}</a>
    @endforeach
</nav>
<div class="customer-order-panel">
@if($orderTab === 'track')
    @if($selectedOrder->status === 'বাতিল')
        <div class="alert alert-danger">{{ $detailText('cancelled') }}</div>
    @endif
    <ol class="customer-order-timeline">
    @foreach($stages as $status => [$label, $icon])
        @php($reached = $stageIndex !== false && $loop->index <= $stageIndex)
        @php($record = $history->where('status', $status)->last())
        <li class="{{ $reached ? 'reached' : '' }} {{ $selectedOrder->status === $status ? 'current' : '' }}" @if($selectedOrder->status === $status) aria-current="step" @endif>
            <span class="customer-step-icon"><i class="bi bi-{{ $icon }}"></i></span><b>{{ $detailText($label) }}</b>
            @if($record)<small><i class="bi bi-clock"></i> {{ \Illuminate\Support\Carbon::parse($record['at'])->format('h:i A, d M Y') }}</small>@elseif($loop->first)<small>{{ $selectedOrder->created_at->format('h:i A, d M Y') }}</small>@endif
        </li>
    @endforeach
    </ol>
    @if($selectedOrder->courier_tracking_url)<a class="customer-text-link" href="{{ $selectedOrder->courier_tracking_url }}" target="_blank" rel="noopener">{{ $detailText('courier_link') }} <i class="bi bi-arrow-up-right"></i></a>@endif
@elseif($orderTab === 'summary')
    <div class="table-responsive"><table class="table customer-table"><thead><tr><th>{{ $detailText('product') }}</th><th>{{ $detailText('quantity') }}</th><th>{{ $detailText('price') }}</th></tr></thead><tbody>@foreach($selectedOrder->items as $item)<tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>৳{{ number_format($item->price * $item->quantity, 2) }}</td></tr>@endforeach</tbody></table></div>
    <dl class="customer-order-totals">@foreach(['subtotal', 'discount', 'delivery_charge', 'total'] as $field)<div><dt>{{ $detailText($field) }}</dt><dd>৳{{ number_format($selectedOrder->{$field}, 2) }}</dd></div>@endforeach</dl>
    <h4>{{ $detailText('address') }}</h4><p>{{ $selectedOrder->name }} · {{ $selectedOrder->phone }}</p><p>{{ $selectedOrder->address }}, {{ $selectedOrder->area }}, {{ $selectedOrder->district }}</p><p>{{ $detailText('payment') }}: {{ strtoupper($selectedOrder->payment_method) }}</p>
@elseif($orderTab === 'vendor')
    <h3>{{ $store->store_name }}</h3>
    @if($store->business_address)<p>{{ $store->business_address }}</p>@endif
    @if($store->support_phone)<p><a href="tel:{{ $store->support_phone }}">{{ $store->support_phone }}</a></p>@endif
    @if($store->support_email)<p><a href="mailto:{{ $store->support_email }}">{{ $store->support_email }}</a></p>@endif
    <a class="customer-text-link" href="{{ route('dashboard', ['section' => 'support']) }}">{{ $detailText('support') }}</a>
@elseif($orderTab === 'delivery')
    @if($selectedOrder->delivery_name || $selectedOrder->delivery_phone || $selectedOrder->courier_tracking_url)
        <h3>{{ $selectedOrder->delivery_name }}</h3>
        @if($selectedOrder->delivery_phone)<p><a href="tel:{{ $selectedOrder->delivery_phone }}">{{ $selectedOrder->delivery_phone }}</a></p>@endif
        @if($selectedOrder->courier_tracking_url)<a class="customer-text-link" href="{{ $selectedOrder->courier_tracking_url }}" target="_blank" rel="noopener">{{ $detailText('courier_link') }}</a>@endif
    @else<p class="text-muted">{{ $detailText('delivery_empty') }}</p>@endif
@elseif($orderTab === 'reviews')
    @foreach($selectedOrder->items as $item)
        <div class="customer-order-review"><b>{{ $item->product_name }}</b>@if($orderProducts->has($item->product_id))<a class="customer-back-button" href="{{ route('product', ['product' => $orderProducts[$item->product_id], 'tab' => 'reviews']) }}#productReviews">{{ $detailText('review') }}</a>@else<small>{{ $detailText('unavailable') }}</small>@endif</div>
    @endforeach
@endif
</div>
