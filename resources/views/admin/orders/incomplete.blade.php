@extends('layouts.admin')
@section('title', ($today ? "Today's" : 'All').' Incomplete Orders — ChairGhor Admin')
@section('content')
<div class="admin-page-heading"><div><span>ORDER MANAGEMENT</span><h1>{{ $today ? "Today's" : 'All' }} Incomplete Orders</h1><p>Customers who entered their details but did not confirm an order.</p></div><b>Total: {{ $orders->total() }}</b></div>
<section class="admin-panel"><div class="table-responsive"><table class="table admin-orders-table align-middle"><thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Email</th><th>Address</th><th>Quantity</th><th>Latest Update</th><th>Actions</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td>{{ $orders->firstItem() + $loop->index }}</td><td>{{ $order->name ?: '—' }}</td><td>{{ $order->phone ?: '—' }}</td><td>{{ $order->email ?: '—' }}</td><td>{{ collect([$order->address, $order->area, $order->district])->filter()->join(', ') ?: '—' }}</td><td>{{ $order->quantity }}</td><td>{{ $order->updated_at->format('d M Y, h:i A') }}</td><td><form method="post" action="{{ route('admin.incomplete-orders.destroy', $order) }}" onsubmit="return confirm('Remove this incomplete order?')">@csrf @method('DELETE')<button class="category-action delete" title="Delete"><i class="bi bi-trash3"></i></button></form></td></tr>
@empty<tr><td colspan="8"><div class="category-empty"><i class="bi bi-folder2-open"></i><b>No incomplete orders found.</b></div></td></tr>@endforelse
</tbody></table></div></section>{{ $orders->links() }}
@endsection
