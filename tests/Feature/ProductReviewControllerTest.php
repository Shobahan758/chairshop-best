<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_publish_a_text_review_with_an_image(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('chair-review.jpg', 900, 700);

        $this->post(route('product.reviews.store', $product), [
            'name' => 'Review Customer',
            'rating' => 5,
            'body' => 'চেয়ারটি দেখতে সুন্দর এবং খুব আরামদায়ক।',
            'image' => $image,
        ])->assertRedirect(route('product', $product).'#productReviews');

        $review = $product->reviews()->firstOrFail();

        $this->assertSame('Review Customer', $review->name);
        $this->assertSame(5, $review->rating);
        $this->assertSame('চেয়ারটি দেখতে সুন্দর এবং খুব আরামদায়ক।', $review->body);
        Storage::disk('public')->assertExists($review->image_path);
    }

    public function test_review_rejects_an_invalid_file(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();

        $this->post(route('product.reviews.store', $product), [
            'name' => 'Review Customer',
            'rating' => 4,
            'body' => 'পণ্যটি ব্যবহার করে ভালো লেগেছে।',
            'image' => UploadedFile::fake()->create('review.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_form_retries_do_not_trigger_the_old_five_request_limit(): void
    {
        $product = Product::factory()->create();

        foreach (range(1, 6) as $attempt) {
            $response = $this->post(route('product.reviews.store', $product), [
                'name' => 'Review Customer '.$attempt,
                'rating' => 5,
                'body' => 'চেয়ারটি ব্যবহার করে আমার ভালো লেগেছে।',
            ]);
        }

        $response->assertRedirect(route('product', $product).'#productReviews');
        $this->assertDatabaseCount('reviews', 6);
    }

    public function test_product_page_escapes_review_text(): void
    {
        $product = Product::factory()->create();
        $product->reviews()->create([
            'name' => '<script>alert("name")</script>',
            'rating' => 4,
            'body' => '<script>alert("review")</script>',
        ]);

        $this->get(route('product', $product))
            ->assertSee('&lt;script&gt;alert(&quot;name&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("review")</script>', false);
    }
}
