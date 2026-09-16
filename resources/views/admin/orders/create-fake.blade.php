@extends('layouts.admin')
@section('title', 'Create Fake Order — ChairGhor Admin')
@section('content')
<div class="admin-page-heading"><div><span>ORDER MANAGEMENT</span><h1>Create Fake Order</h1><p>Add a test or identified fake order without changing product stock.</p></div></div>
<section class="admin-panel p-4"><form method="post" action="{{ route('admin.fake-orders.store') }}">@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Customer name *</label><input class="form-control" name="name" value="{{ old('name') }}" required></div><div class="col-md-6"><label class="form-label">Phone *</label><input class="form-control" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email') }}"></div><div class="col-md-6"><label class="form-label">District *</label><input class="form-control" name="district" value="{{ old('district') }}" required></div>
<div class="col-md-6"><label class="form-label">Area *</label><input class="form-control" name="area" value="{{ old('area') }}" required></div><div class="col-md-6"><label class="form-label">Address *</label><input class="form-control" name="address" value="{{ old('address') }}" required></div>
<div class="col-md-6"><label class="form-label">Product *</label><select class="form-select" name="product_id" required><option value="">Choose a product</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} — ৳{{ number_format((float) $product->current_price) }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Quantity *</label><input class="form-control" type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required></div><div class="col-md-3"><label class="form-label">Status *</label><select class="form-select" name="status">@foreach(['অর্ডার গ্রহণ','নিশ্চিত','প্রস্তুত','পাঠানো','ডেলিভারি সম্পন্ন','বাতিল'] as $status)<option @selected(old('status') === $status)>{{ $status }}</option>@endforeach</select></div>
</div>@if($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif<div class="mt-4 d-flex gap-2"><a class="btn btn-admin-light" href="{{ route('admin.fake-orders.index') }}">Cancel</a><button class="btn btn-admin-primary" type="submit"><i class="bi bi-check2"></i> Save fake order</button></div></form></section>
@endsection
