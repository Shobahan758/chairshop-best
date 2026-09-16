@extends('layouts.admin')

@section('title', 'Edit Order - ChairGhor Admin')

@section('content')
<div class="admin-page-heading">
    <div><span>ORDER MANAGEMENT</span><h1>Edit Order</h1><p>{{ $order->order_number }}</p></div>
    <a class="btn btn-admin-light" href="{{ route('admin.orders.index') }}">Back to orders</a>
</div>
<section class="admin-panel p-4">
    <div class="mb-4">
        @foreach($order->items as $item)
            <div>{{ $item->product_name }} &times; {{ $item->quantity }}</div>
        @endforeach
        <b>Total: ৳{{ number_format((float) $order->total) }}</b>
    </div>
    <form method="post" action="{{ route('admin.orders.update', $order) }}">
        @csrf
        @method('PUT')
        <div class="row g-3">
            @foreach(['name' => 'Customer name', 'phone' => 'Phone', 'email' => 'Email', 'district' => 'District', 'area' => 'Area'] as $field => $label)
                <div class="col-md-6">
                    <label class="form-label" for="order-{{ $field }}">{{ $label }}{{ $field === 'email' ? '' : ' *' }}</label>
                    <input id="order-{{ $field }}" class="form-control @error($field) is-invalid @enderror" type="{{ $field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text') }}" name="{{ $field }}" value="{{ old($field, $order->{$field}) }}" @required($field !== 'email')>
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
            <div class="col-md-6">
                <label class="form-label" for="order-status">Status *</label>
                <select id="order-status" class="form-select @error('status') is-invalid @enderror" name="status" required>
                    @foreach(['অর্ডার গ্রহণ' => 'New Order', 'নিশ্চিত' => 'Confirmed', 'প্রস্তুত' => 'Processing', 'পাঠানো' => 'Shipped', 'ডেলিভারি সম্পন্ন' => 'Completed', 'বাতিল' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $order->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @foreach(['delivery_name' => 'Courier / delivery person', 'delivery_phone' => 'Delivery phone', 'courier_tracking_url' => 'Courier tracking URL'] as $field => $label)
                <div class="col-md-6"><label class="form-label" for="order-{{ $field }}">{{ $label }}</label><input class="form-control" id="order-{{ $field }}" name="{{ $field }}" value="{{ old($field, $order->{$field}) }}" type="{{ $field === 'courier_tracking_url' ? 'url' : 'text' }}">@error($field)<div class="text-danger small">{{ $message }}</div>@enderror</div>
            @endforeach
            @foreach(['address' => 'Address', 'note' => 'Note'] as $field => $label)
                <div class="col-12">
                    <label class="form-label" for="order-{{ $field }}">{{ $label }}{{ $field === 'address' ? ' *' : '' }}</label>
                    <textarea id="order-{{ $field }}" class="form-control @error($field) is-invalid @enderror" name="{{ $field }}" rows="3" @required($field === 'address')>{{ old($field, $order->{$field}) }}</textarea>
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>
        <div class="mt-4 d-flex gap-2">
            <a class="btn btn-admin-light" href="{{ route('admin.orders.index') }}">Cancel</a>
            <button class="btn btn-admin-primary" type="submit"><i class="bi bi-check2"></i> Save changes</button>
        </div>
    </form>
</section>
@endsection
