@extends('layouts.admin')

@section('title', $title.' - ChairGhor Admin')

@section('content')
<div class="admin-page-heading">
    <div><span>ORDER MANAGEMENT</span><h1>{{ $title }}</h1></div>
    <b>Total: {{ $orders->total() }}</b>
</div>
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
@php
    $statusLabels = ['অর্ডার গ্রহণ' => 'New Order', 'নিশ্চিত' => 'Confirmed', 'প্রস্তুত' => 'Processing', 'পাঠানো' => 'Shipped', 'ডেলিভারি সম্পন্ন' => 'Completed', 'বাতিল' => 'Cancelled'];
@endphp
<section class="admin-panel">
    <div class="table-responsive">
        <table class="table admin-orders-table align-middle">
            <thead><tr><th>Order</th><th>Customer</th><th>Products</th><th>Address</th><th>Date</th><th>Total</th><th>Risk score</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><b>{{ $order->order_number }}</b></td>
                        <td><div class="customer-cell"><p><b>{{ $order->name }}</b><small>{{ $order->phone }}</small><small>{{ $order->email }}</small></p></div></td>
                        <td>@foreach($order->items as $item)<div>{{ $item->product_name }} &times; {{ $item->quantity }}</div>@endforeach</td>
                        <td>{{ $order->address }}, {{ $order->area }}, {{ $order->district }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td><b>৳{{ number_format((float) $order->total) }}</b></td>
                        <td>@include('admin.orders.risk')</td>
                        <td>
                            <form method="post" action="{{ route('admin.order', $order) }}" class="d-flex align-items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select class="admin-status-select" name="status" aria-label="Status for {{ $order->order_number }}">
                                    @foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected($value === $order->status)>{{ $label }}</option>@endforeach
                                </select>
                                <button type="submit" class="btn btn-admin-light btn-sm" title="Save status" aria-label="Save status for {{ $order->order_number }}"><i class="bi bi-check-lg"></i></button>
                            </form>
                        </td>
                        <td>
                            <div class="category-actions">
                                <a class="category-action edit" href="{{ route('admin.orders.edit', $order) }}" title="Edit" aria-label="Edit {{ $order->order_number }}"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete this order? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="category-action delete" type="submit" title="Delete" aria-label="Delete {{ $order->order_number }}"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
{{ $orders->links() }}
@endsection
