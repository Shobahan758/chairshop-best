@extends('layouts.admin')
@php($isEditing = isset($product))
@section('title', ($isEditing ? 'Edit' : 'Add').' Product — ChairGhor Admin')

@section('content')
<div class="product-create-heading">
    <div>
        <a href="{{ route('admin.products.index') }}"><i class="bi bi-arrow-left"></i> Products</a>
        <h1>{{ $isEditing ? 'Edit product' : 'Create product' }}</h1>
        <p>{{ $isEditing ? 'Update this product and its storefront details.' : 'Add a new chair and organize it in your catalog.' }}</p>
    </div>
</div>

<form method="post" action="{{ $isEditing ? route('admin.products.update', $product) : route('admin.products.store') }}" class="product-create-form" enctype="multipart/form-data" data-image-upload-form>
    @csrf
    @if($isEditing) @method('PUT') @endif
    <div class="product-create-card">
    <div class="product-create-grid">
        <div class="product-create-main">
            <section class="product-create-section">
                <div class="product-section-title">
                    <span><i class="bi bi-box-seam"></i></span>
                    <div><h2>Product information</h2><p>Name and organize this item in your catalog.</p></div>
                </div>
                <div class="row g-3 product-fields">
                    <div class="col-md-4">
                        <label class="form-label" for="productSku">SKU <b>*</b></label>
                        <input class="form-control @error('sku') is-invalid @enderror" id="productSku" name="sku" value="{{ $product->sku ?? '' }}" @if(! $isEditing) data-sku-number="{{ $nextSkuNumber }}" @endif placeholder="Select category to generate SKU" readonly>
                        <small class="form-text">{{ $isEditing ? 'Assigned automatically' : 'Generated automatically; final serial assigned on save' }}</small>
                        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="productName">Product name <b>*</b></label>
                        <input class="form-control @error('name') is-invalid @enderror" id="productName" name="name" value="{{ old('name', $product->name ?? '') }}" placeholder="e.g. Premium Emerald Chair" data-slug-target="#productSlug" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="productCategory">Category <b>*</b></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="productCategory" name="category_id" data-category-select required>
                            <option value="">Choose a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" data-sku-initial="{{ \App\Services\ProductSkuGenerator::initial($category->name) }}" @selected(old('category_id', $product->category_id ?? null) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="productSubcategory">Subcategory</label>
                        <select class="form-select @error('subcategory_id') is-invalid @enderror" id="productSubcategory" name="subcategory_id" data-subcategory-select>
                            <option value="">Choose a subcategory</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" data-category-id="{{ $subcategory->category_id }}" data-sku-initial="{{ \App\Services\ProductSkuGenerator::initial($subcategory->name) }}" @selected(old('subcategory_id', $product->subcategory_id ?? null) == $subcategory->id)>{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                        @error('subcategory_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="productBrand">Brand</label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" id="productBrand" name="brand_id">
                            <option value="">Choose a brand</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id ?? null) == $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="productSlug">Slug</label>
                        <div class="input-group slug-input">
                            <span class="input-group-text">/chair/</span>
                            <input class="form-control @error('slug') is-invalid @enderror" id="productSlug" name="slug" value="{{ old('slug', $product->slug ?? '') }}" placeholder="premium-emerald-chair">
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-12"><label class="form-label" for="shortDescription">Short description</label><textarea class="form-control" id="shortDescription" name="short_description" rows="2">{{ old('short_description', $product->short_description ?? '') }}</textarea></div>
                    <div class="col-12"><label class="form-label" for="productDescription">Description</label><textarea class="form-control" id="productDescription" name="description" rows="4">{{ old('description', $product->description ?? '') }}</textarea></div>
                </div>
            </section>

            <section class="product-create-section">
                <div class="product-section-title">
                    <span><i class="bi bi-cash-stack"></i></span>
                    <div><h2>Pricing & inventory</h2><p>Set the selling price and available stock.</p></div>
                </div>
                <div class="row g-3 product-fields">
                    <div class="col-sm-6 col-xl-3">
                        <label class="form-label" for="productPrice">Regular price <b>*</b></label>
                        <div class="input-group"><span class="input-group-text">৳</span><input class="form-control @error('price') is-invalid @enderror" id="productPrice" name="price" type="number" min="0" step="0.01" value="{{ old('price', $product->price ?? '') }}" placeholder="0.00" data-regular-price required>@error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <label class="form-label" for="discountPercentage">Discount</label>
                        <div class="input-group"><input class="form-control @error('discount_percentage') is-invalid @enderror" id="discountPercentage" name="discount_percentage" type="number" min="0" max="100" step="0.01" value="{{ old('discount_percentage', $isEditing && $product->sale_price ? round((1 - ($product->sale_price / $product->price)) * 100, 2) : '') }}" placeholder="0" data-discount-percentage><span class="input-group-text">%</span>@error('discount_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <label class="form-label" for="salePrice">Sale price</label>
                        <div class="input-group"><span class="input-group-text">৳</span><input class="form-control @error('sale_price') is-invalid @enderror" id="salePrice" name="sale_price" type="number" min="0" step="0.01" value="{{ old('sale_price', $product->sale_price ?? '') }}" placeholder="0.00" data-sale-price>@error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <label class="form-label" for="productStock">Stock quantity <b>*</b></label>
                        <input class="form-control @error('stock') is-invalid @enderror" id="productStock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required>
                        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="material">Material <small class="text-muted">(optional)</small></label>
                        <input class="form-control @error('material') is-invalid @enderror" id="material" name="material" data-tag-input value="{{ old('material', $product->material ?? '') }}" placeholder="e.g. Wood, fabric">
                        <small class="form-text" id="materialHelp">Press Enter or comma to add a tag. Click × to remove.</small>
                        @error('material')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="color">Available color <small class="text-muted">(optional)</small></label>
                        <input class="form-control @error('color') is-invalid @enderror" id="color" name="color" data-tag-input value="{{ old('color', $product->color ?? '') }}" placeholder="e.g. Black, White, Emerald">
                        <small class="form-text" id="colorHelp">Press Enter or comma to add a tag. Click × to remove.</small>
                        @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="productSize">Size <small class="text-muted">(optional)</small></label>
                        <input class="form-control @error('size') is-invalid @enderror" id="productSize" name="size" data-tag-input value="{{ old('size', $product->size ?? '') }}" placeholder="e.g. 60 × 60 × 90 cm" maxlength="255">
                        <small class="form-text" id="productSizeHelp">Press Enter or comma to add a tag. Click × to remove.</small>
                        @error('size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="product-create-section">
                <div class="product-section-title">
                    <span><i class="bi bi-images"></i></span>
                    <div><h2>Additional product images</h2><p>Add more views of this product.</p></div>
                </div>
                <div class="product-fields additional-image-grid" data-additional-image-grid>
                    @foreach (range(0, max(4, count($product->additional_images ?? [])) - 1) as $imageIndex)
                        <div class="additional-image-field">
                            <label class="additional-image-preview" for="additionalImage{{ $imageIndex }}" data-additional-image-preview>
                                <img src="{{ $product->additional_images[$imageIndex] ?? '' }}" alt="Additional image {{ $loop->iteration }} preview" @if(empty($product->additional_images[$imageIndex] ?? null)) hidden @endif>
                                <span @if(! empty($product->additional_images[$imageIndex] ?? null)) hidden @endif><i class="bi bi-cloud-arrow-up"></i> Upload image {{ $loop->iteration }}</span>
                            </label>
                            <label class="form-label" for="additionalImage{{ $imageIndex }}">Image {{ $loop->iteration }}</label>
                            <input class="visually-hidden @error("additional_images.$imageIndex") is-invalid @enderror" id="additionalImage{{ $imageIndex }}" name="additional_images[{{ $imageIndex }}]" type="file" accept="image/jpeg,image/png,image/webp" data-additional-image-source>
                            <button class="btn btn-admin-light w-100" type="button" data-additional-image-button aria-controls="additionalImage{{ $imageIndex }}" aria-label="Choose additional image {{ $loop->iteration }}"><i class="bi bi-plus-lg"></i> <span>{{ empty($product->additional_images[$imageIndex] ?? null) ? 'Choose image' : 'Change image' }}</span></button>
                            <small class="image-upload-help" data-additional-image-name aria-live="polite"></small>
                            <small class="image-upload-help">Recommended: 1200 × 1200 px · Auto-compressed to max 40 KB</small>
                            @error("additional_images.$imageIndex")<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>
                <div class="product-fields">
                    <button class="btn btn-admin-primary" type="button" data-add-image-field><i class="bi bi-plus-lg"></i> Add image</button>
                </div>
            </section>

        </div>

        <aside class="product-create-side">
            <section class="product-create-section">
                <div class="product-section-title">
                    <span><i class="bi bi-image"></i></span>
                    <div><h2>Product image</h2><p>Add the main storefront image.</p></div>
                </div>
                <div class="product-fields">
                    <label class="product-image-preview" for="productImage" data-image-preview>
                        <img src="{{ $product->image ?? '' }}" alt="Product preview" @if(! $isEditing) hidden @endif>
                        <span @if($isEditing) hidden @endif><i class="bi bi-cloud-arrow-up"></i><b>Main product image</b><small>Choose an image from your device</small></span>
                        <em>PRIMARY</em>
                    </label>
                    <label class="form-label mt-3" for="productImage">Main image <b>*</b></label>
                    <input class="form-control @error('image') is-invalid @enderror" id="productImage" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-source @required(! $isEditing)>
                    <small class="image-upload-help">Recommended: 1200 × 1200 px · JPG, PNG or WebP · Auto-compressed to max 40 KB</small>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </section>

            <section class="product-create-section product-publish-card">
                <div class="product-section-title">
                    <span><i class="bi bi-stars"></i></span>
                    <div><h2>Storefront visibility</h2><p>Choose how this product appears.</p></div>
                </div>
                <div class="product-fields product-visibility-options">
                    <label class="product-toggle" for="featured">
                        <span><b>জনপ্রিয় পছন্দ</b><small>Show this product in the Popular Choices section.</small></span>
                        <input class="form-check-input" id="featured" name="featured" type="checkbox" value="1" @checked(old('featured', $product->featured ?? false))>
                    </label>
                    <label class="product-toggle" for="justForYou">
                        <span><b>Just For You</b><small>Show this product in the Just For You section.</small></span>
                        <input class="form-check-input" id="justForYou" name="just_for_you" type="checkbox" value="1" @checked(old('just_for_you', $product->just_for_you ?? false))>
                    </label>
                    <label class="product-toggle" for="officeEssential">
                        <span><b>অফিস এসেনশিয়ালস</b><small>Show this product in the Office Essentials section.</small></span>
                        <input class="form-check-input" id="officeEssential" name="office_essential" type="checkbox" value="1" @checked(old('office_essential', $product->office_essential ?? false))>
                    </label>
                    <label class="product-toggle" for="gamingPick">
                        <span><b>গেমিং পিকস</b><small>Show this product in the Gaming Picks section.</small></span>
                        <input class="form-check-input" id="gamingPick" name="gaming_pick" type="checkbox" value="1" @checked(old('gaming_pick', $product->gaming_pick ?? false))>
                    </label>
                    <div class="alert alert-danger" role="alert" data-product-form-error @if(! $errors->any()) hidden @endif>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    <div class="product-form-actions">
                        <a class="btn btn-admin-light" href="{{ route('admin.products.index') }}">Cancel</a>
                        <button class="btn btn-admin-primary" type="submit"><i class="bi bi-check2"></i> {{ $isEditing ? 'Update product' : 'Save product' }}</button>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    </div>
</form>
@endsection
