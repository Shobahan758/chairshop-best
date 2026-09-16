<!doctype html>
<html lang="bn">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $order->order_number }} — Invoice</title>@vite(['resources/css/app.css'])</head>
<body class="customer-invoice">
@php($invoiceText = fn (string $key): string => $siteContent->text('account', 'order_details', $key))
<main>
    <button class="customer-back-button invoice-print-button" type="button" onclick="window.print()">{{ $invoiceText('print') }}</button>
    <h1>{{ $store->store_name }}</h1><p>{{ $store->business_address }}</p><p>{{ $store->support_phone }} {{ $store->support_email }}</p>
    <hr><h2>{{ $invoiceText('invoice') }} #{{ $order->order_number }}</h2><p>{{ $invoiceText('date') }}: {{ $order->created_at->format('d M Y, h:i A') }}</p>
    <h3>{{ $invoiceText('customer') }}</h3><p>{{ $order->name }} · {{ $order->phone }}</p><p>{{ $order->address }}, {{ $order->area }}, {{ $order->district }}</p>
    <table><thead><tr><th>{{ $invoiceText('product') }}</th><th>{{ $invoiceText('quantity') }}</th><th>{{ $invoiceText('price') }}</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td><td>৳{{ number_format($item->price * $item->quantity, 2) }}</td></tr>@endforeach</tbody></table>
    <dl class="customer-order-totals">@foreach(['subtotal', 'discount', 'delivery_charge', 'total'] as $field)<div><dt>{{ $invoiceText($field) }}</dt><dd>৳{{ number_format($order->{$field}, 2) }}</dd></div>@endforeach</dl>
    <p>{{ $invoiceText('payment') }}: {{ strtoupper($order->payment_method) }} · {{ $order->status }}</p>
</main>
</body></html>
