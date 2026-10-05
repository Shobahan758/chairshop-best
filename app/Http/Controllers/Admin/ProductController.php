<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\ProductImageOptimizer;
use App\Services\ProductSkuGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')->latest();
        $title = 'All Products';

        if ($request->routeIs('admin.products.low-stock')) {
            $products->where('stock', '<', 10)->orderBy('stock');
            $title = 'Low Stock Products';
        } elseif ($request->routeIs('admin.products.sale')) {
            $products->whereNotNull('sale_price');
            $title = 'Sale Products';
        }

        return view('admin.products.index', ['products' => $products->paginate(15), 'title' => $title]);
    }

    public function create(ProductSkuGenerator $skuGenerator): View
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('name')->get(),
            'subcategories' => Subcategory::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'nextSkuNumber' => $skuGenerator->nextNumber(),
        ]);
    }

    public function store(StoreProductRequest $request, ProductImageOptimizer $imageOptimizer, ProductSkuGenerator $skuGenerator): RedirectResponse
    {
        $attributes = $request->safe()->except(['discount_percentage', 'additional_images']);
        $newImages = [];

        try {
            $attributes['image'] = $imageOptimizer->store($request->file('image'));
            $newImages[] = $attributes['image'];
            $attributes['additional_images'] = [];
            foreach ($request->file('additional_images', []) as $image) {
                $newImage = $imageOptimizer->store($image);
                $newImages[] = $newImage;
                $attributes['additional_images'][] = $newImage;
            }

            $attributes['sku'] = $skuGenerator->generate(
                Category::findOrFail($attributes['category_id']),
                isset($attributes['subcategory_id']) ? Subcategory::findOrFail($attributes['subcategory_id']) : null,
            );

            Product::create($attributes);
        } catch (Throwable $exception) {
            foreach ($newImages as $image) {
                $imageOptimizer->delete($image);
            }

            throw $exception;
        }

        return redirect()->route('admin.products.index')->with('success', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.create', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'subcategories' => Subcategory::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, ProductImageOptimizer $imageOptimizer): RedirectResponse
    {
        $attributes = $request->safe()->except(['discount_percentage', 'additional_images', 'image']);

        $newImages = [];
        $replacedImages = [];

        try {
            if ($request->hasFile('image')) {
                $attributes['image'] = $imageOptimizer->store($request->file('image'));
                $newImages[] = $attributes['image'];
                $replacedImages[] = $product->image;
            }

            $additionalImages = collect($request->file('additional_images', []))->filter();
            if ($additionalImages->isNotEmpty()) {
                $images = $product->additional_images ?? [];
                foreach ($additionalImages as $index => $image) {
                    $newImage = $imageOptimizer->store($image);
                    $newImages[] = $newImage;
                    if (isset($images[$index])) {
                        $replacedImages[] = $images[$index];
                    }
                    $images[$index] = $newImage;
                }
                ksort($images);
                $attributes['additional_images'] = array_values($images);
            }

            $product->update($attributes);
        } catch (Throwable $exception) {
            foreach ($newImages as $image) {
                $imageOptimizer->delete($image);
            }

            throw $exception;
        }

        foreach ($replacedImages as $image) {
            $imageOptimizer->delete($image);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'Product status updated successfully.');
    }

    public function destroy(Product $product, ProductImageOptimizer $imageOptimizer): RedirectResponse
    {
        $imageOptimizer->delete($product->image);
        collect($product->additional_images)->each(function (string $image) use ($imageOptimizer): void {
            $imageOptimizer->delete($image);
        });
        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }
}
