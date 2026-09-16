@extends('layouts.admin')

@section('title', 'Categories — ChairGhor Admin')

@section('content')
<div class="admin-page-heading">
    <div>
<span>CATALOG MANAGEMENT</span>
<h1>Categories</h1>
<p>Create and manage the product categories shown in your store.</p>
</div>
    <div>
<button class="btn btn-admin-primary" type="button" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
<i class="bi bi-plus-lg">
</i> Add Category</button>
</div>
</div>

<section class="admin-panel">
    <div class="admin-panel-head">
<div>
<span class="panel-kicker">ALL CATEGORIES</span>
<h2>Category List</h2>
</div>
<span class="category-count">{{ $categories->count() }} total</span>
</div>
    <div class="table-responsive">
<table class="table admin-orders-table align-middle category-table">
        <thead>
<tr>
<th>Category</th>
<th>Slug</th>
<th>Products</th>
<th>Status</th>
<th>Created</th>
<th class="text-end">Actions</th>
</tr>
</thead>
        <tbody>@forelse($categories as $category)<tr>
<td>
<div class="category-name">
<span class="category-icon">
<i class="bi bi-{{ $category->icon ?: 'tag' }}">
</i>
</span>
<b>{{ $category->name }}</b>
</div>
</td>
<td>
<code>{{ $category->slug }}</code>
</td>
<td>{{ $category->products_count }}</td>
<td>
<span class="category-status {{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
</td>
<td>{{ $category->created_at->format('d M, Y') }}</td>
<td>
<div class="category-actions">
<button class="category-action edit" type="button" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}" aria-label="Edit {{ $category->name }}" title="Edit">
<i class="bi bi-pencil">
</i>
</button>
<form method="post" action="{{ route('admin.categories.toggle', $category) }}">@csrf @method('PATCH')<button class="category-action toggle" type="submit" aria-label="{{ $category->is_active ? 'Deactivate' : 'Activate' }} {{ $category->name }}" title="{{ $category->is_active ? 'Make inactive' : 'Make active' }}">
<i class="bi bi-{{ $category->is_active ? 'pause-circle' : 'play-circle' }}">
</i>
</button>
</form>
<form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? This action cannot be undone.')">@csrf @method('DELETE')<button class="category-action delete" type="submit" aria-label="Delete {{ $category->name }}" title="Delete">
<i class="bi bi-trash3">
</i>
</button>
</form>
</div>
</td>
</tr>@empty<tr>
<td colspan="6">
<div class="category-empty">
<i class="bi bi-tags">
</i>
<b>No categories yet</b>
<span>Add your first category to organize products.</span>
</div>
</td>
</tr>@endforelse</tbody>
    </table>
</div>
</section>

@foreach($categories as $category)
<div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{ $category->id }}" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content admin-modal">
    <div class="modal-header">
<div>
<span class="panel-kicker">EDIT CATEGORY</span>
<h2 class="modal-title" id="editCategoryModalLabel{{ $category->id }}">Update Category</h2>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
</button>
</div>
    <form method="post" action="{{ route('admin.categories.update', $category) }}">@csrf @method('PUT')
        <div class="modal-body">
<div class="mb-3">
<label class="form-label" for="categoryName{{ $category->id }}">Category Name <span>*</span>
</label>
<input class="form-control" id="categoryName{{ $category->id }}" name="name" value="{{ $category->name }}" data-slug-target="#categorySlug{{ $category->id }}" required>
</div>
<div class="mb-3">
<label class="form-label" for="categorySlug{{ $category->id }}">Slug <small class="text-muted">(optional)</small>
</label>
<input class="form-control" id="categorySlug{{ $category->id }}" name="slug" value="{{ $category->slug }}" placeholder="Generated automatically">
</div>
<div>
<label class="form-label" for="categoryIcon{{ $category->id }}">Bootstrap Icon</label>
<div class="input-group">
<span class="input-group-text">
<i class="bi bi-bootstrap">
</i>
</span>
<input class="form-control" id="categoryIcon{{ $category->id }}" name="icon" value="{{ $category->icon }}">
</div>
</div>
</div>
        <div class="modal-footer">
<button type="button" class="btn btn-admin-light" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-admin-primary">
<i class="bi bi-check2">
</i> Update Category</button>
</div>
    </form>
</div>
</div>
</div>
@endforeach

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content admin-modal">
    <div class="modal-header">
<div>
<span class="panel-kicker">NEW CATEGORY</span>
<h2 class="modal-title" id="addCategoryModalLabel">Add Category</h2>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
</button>
</div>
    <form method="post" action="{{ route('admin.categories.store') }}">@csrf
        <div class="modal-body">
            <div class="mb-3">
<label class="form-label" for="categoryName">Category Name <span>*</span>
</label>
<input class="form-control @error('name') is-invalid @enderror" id="categoryName" name="name" value="{{ old('name') }}" placeholder="e.g. Office Chairs" data-slug-target="#categorySlug" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3">
<label class="form-label" for="categorySlug">Slug <small class="text-muted">(optional)</small>
</label>
<input class="form-control @error('slug') is-invalid @enderror" id="categorySlug" name="slug" value="{{ old('slug') }}" placeholder="Generated automatically">@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror<small class="form-hint">Leave blank to generate from the category name.</small>
</div>
            <div>
<label class="form-label" for="categoryIcon">Bootstrap Icon</label>
<div class="input-group">
<span class="input-group-text">
<i class="bi bi-bootstrap">
</i>
</span>
<input class="form-control @error('icon') is-invalid @enderror" id="categoryIcon" name="icon" value="{{ old('icon') }}" placeholder="e.g. chair, briefcase, controller">@error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<small class="form-hint">Enter the icon name without “bi-”.</small>
</div>
        </div>
        <div class="modal-footer">
<button type="button" class="btn btn-admin-light" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-admin-primary">
<i class="bi bi-check2">
</i> Save Category</button>
</div>
    </form>
</div>
</div>
</div>
@endsection
