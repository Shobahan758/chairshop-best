@extends('layouts.admin')
@section('title', 'Sub Categories — ChairGhor Admin')
@section('content')
<div class="admin-page-heading">
<div>
<span>CATALOG MANAGEMENT</span>
<h1>Sub Categories</h1>
<p>Organize products under a parent category.</p>
</div>
<div>
<button class="btn btn-admin-primary" type="button" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
<i class="bi bi-plus-lg">
</i> Add Sub Category</button>
</div>
</div>
<section class="admin-panel">
<div class="admin-panel-head">
<div>
<span class="panel-kicker">ALL SUB CATEGORIES</span>
<h2>Sub Category List</h2>
</div>
<span class="category-count">{{$subcategories->count()}} total</span>
</div>
<div class="table-responsive">
<table class="table admin-orders-table align-middle category-table">
<thead>
<tr>
<th>Sub Category</th>
<th>Parent Category</th>
<th>Slug</th>
<th>Created</th>
</tr>
</thead>
<tbody>@forelse($subcategories as $subcategory)<tr>
<td>
<div class="category-name">
<span class="category-icon">
<i class="bi bi-diagram-2">
</i>
</span>
<b>{{$subcategory->name}}</b>
</div>
</td>
<td>{{$subcategory->category->name}}</td>
<td>
<code>{{$subcategory->slug}}</code>
</td>
<td>{{$subcategory->created_at->format('d M, Y')}}</td>
</tr>@empty<tr>
<td colspan="4">
<div class="category-empty">
<i class="bi bi-diagram-2">
</i>
<b>No sub categories yet</b>
<span>Add your first sub category.</span>
</div>
</td>
</tr>@endforelse</tbody>
</table>
</div>
</section>
<div class="modal fade" id="addSubcategoryModal" tabindex="-1" aria-labelledby="addSubcategoryModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content admin-modal">
<div class="modal-header">
<div>
<span class="panel-kicker">NEW SUB CATEGORY</span>
<h2 class="modal-title" id="addSubcategoryModalLabel">Add Sub Category</h2>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
</button>
</div>
<form method="post" action="{{route('admin.subcategories.store')}}">@csrf<div class="modal-body">
<div class="mb-3">
<label class="form-label" for="parentCategory">Parent Category <span>*</span>
</label>
<select class="form-select @error('category_id') is-invalid @enderror" id="parentCategory" name="category_id" required>
<option value="">Select category</option>@foreach($categories as $category)<option value="{{$category->id}}" @selected(old('category_id') == $category->id)>{{$category->name}}</option>@endforeach</select>@error('category_id')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
<div class="mb-3">
<label class="form-label" for="subcategoryName">Sub Category Name <span>*</span>
</label>
<input class="form-control @error('name') is-invalid @enderror" id="subcategoryName" name="name" value="{{old('name')}}" placeholder="e.g. Executive Chairs" data-slug-target="#subcategorySlug" required>@error('name')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
<div>
<label class="form-label" for="subcategorySlug">Slug <small class="text-muted">(optional)</small>
</label>
<input class="form-control @error('slug') is-invalid @enderror" id="subcategorySlug" name="slug" value="{{old('slug')}}" placeholder="Generated automatically">@error('slug')<div class="invalid-feedback">{{$message}}</div>@enderror</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-admin-light" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-admin-primary">
<i class="bi bi-check2">
</i> Save Sub Category</button>
</div>
</form>
</div>
</div>
</div>
@endsection
