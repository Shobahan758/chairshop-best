@extends('layouts.admin')
@section('title', 'Brands — ChairGhor Admin')
@section('content')
<div class="admin-page-heading">
<div>
<span>CATALOG MANAGEMENT</span>
<h1>Brands</h1>
<p>Create and manage product brands.</p>
</div>
<div>
<button class="btn btn-admin-primary" type="button" data-bs-toggle="modal" data-bs-target="#addBrandModal">
<i class="bi bi-plus-lg">
</i> Add Brand</button>
</div>
</div>
<section class="admin-panel">
<div class="admin-panel-head">
<div>
<span class="panel-kicker">ALL BRANDS</span>
<h2>Brand List</h2>
</div>
<span class="category-count">{{$brands->count()}} total</span>
</div>
<div class="table-responsive">
<table class="table admin-orders-table align-middle category-table">
<thead>
<tr>
<th>Brand</th>
<th>Slug</th>
<th>Logo</th>
<th>Created</th>
</tr>
</thead>
<tbody>@forelse($brands as $brand)<tr>
<td>
<div class="category-name">
<span class="category-icon">
<i class="bi bi-patch-check">
</i>
</span>
<b>{{$brand->name}}</b>
</div>
</td>
<td>
<code>{{$brand->slug}}</code>
</td>
<td>@if($brand->logo)<a class="panel-link" href="{{$brand->logo}}" target="_blank" rel="noopener">View logo</a>@else<span class="text-muted">—</span>@endif</td>
<td>{{$brand->created_at->format('d M, Y')}}</td>
</tr>@empty<tr>
<td colspan="4">
<div class="category-empty">
<i class="bi bi-patch-check">
</i>
<b>No brands yet</b>
<span>Add your first product brand.</span>
</div>
</td>
</tr>@endforelse</tbody>
</table>
</div>
</section>
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content admin-modal">
<div class="modal-header">
<div>
<span class="panel-kicker">NEW BRAND</span>
<h2 class="modal-title" id="addBrandModalLabel">Add Brand</h2>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
</button>
</div>
<form method="post" action="{{route('admin.brands.store')}}">@csrf<div class="modal-body">
<div class="mb-3">
<label class="form-label" for="brandName">Brand Name <span>*</span>
</label>
<input class="form-control @error('name') is-invalid @enderror" id="brandName" name="name" value="{{old('name')}}" placeholder="e.g. ChairGhor" data-slug-target="#brandSlug" required>@error('name')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
<div class="mb-3">
<label class="form-label" for="brandSlug">Slug <small class="text-muted">(optional)</small>
</label>
<input class="form-control @error('slug') is-invalid @enderror" id="brandSlug" name="slug" value="{{old('slug')}}" placeholder="Generated automatically">@error('slug')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
<div>
<label class="form-label" for="brandLogo">Logo URL</label>
<input class="form-control @error('logo') is-invalid @enderror" id="brandLogo" name="logo" type="url" value="{{old('logo')}}" placeholder="https://example.com/logo.png">@error('logo')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-admin-light" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-admin-primary">
<i class="bi bi-check2">
</i> Save Brand</button>
</div>
</form>
</div>
</div>
</div>
@endsection
