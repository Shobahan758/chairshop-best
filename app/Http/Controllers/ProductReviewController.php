<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductReviewRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function store(StoreProductReviewRequest $request, Product $product): RedirectResponse
    {
        $imagePath = $request->file('image')?->store('reviews', 'public');

        $product->reviews()->create([
            ...$request->safe()->only(['name', 'rating', 'body']),
            'image_path' => $imagePath,
        ]);

        return redirect()->to(route('product', $product).'#productReviews')
            ->with('review_success', 'আপনার রিভিউ সফলভাবে প্রকাশ হয়েছে।');
    }
}
