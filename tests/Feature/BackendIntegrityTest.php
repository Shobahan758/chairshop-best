<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomerAccount;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class BackendIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_success_is_hidden_from_unrelated_guests(): void
    {
        $order = $this->order();

        $this->get(route('order.success', $order))->assertNotFound();
    }

    public function test_guest_can_view_success_for_an_order_in_their_session(): void
    {
        $order = $this->order();

        $this->withSession(['customer_order_ids' => [$order->id]])
            ->get(route('order.success', $order))->assertSee($order->order_number);
    }

    public function test_customer_account_cannot_view_another_accounts_order_success(): void
    {
        $account = CustomerAccount::factory()->create();
        $order = $this->order();

        $this->withSession(['customer_account_id' => $account->id])
            ->get(route('order.success', $order))->assertNotFound();
    }

    public function test_signed_in_customer_can_view_their_order_success(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $order = $this->order(['email' => $user->email]);

        $this->actingAs($user)->get(route('order.success', $order))->assertSee($order->order_number);
    }

    public function test_cart_rejects_a_product_from_an_inactive_category(): void
    {
        $product = Product::factory()->for(Category::factory()->create(['is_active' => false]))->create(['stock' => 5]);

        $this->post(route('cart.add', $product))->assertUnprocessable();
        $this->assertSame([], session('cart', []));
        $this->assertDatabaseCount('tracking_events', 0);
    }

    #[TestWith([0])]
    #[TestWith([-2])]
    #[TestWith(['two'])]
    #[TestWith([[2]])]
    public function test_cart_rejects_invalid_quantities(mixed $quantity): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.add', $product), ['quantity' => $quantity])->assertSessionHasErrors('quantity');
        $this->assertSame([], session('cart', []));
    }

    public function test_review_rejects_an_unavailable_product(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $this->post(route('product.reviews.store', $product), ['name' => 'Buyer', 'rating' => 5, 'body' => 'A comfortable chair'])
            ->assertNotFound();
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_review_rejects_a_product_from_an_inactive_category(): void
    {
        $product = Product::factory()->for(Category::factory()->create(['is_active' => false]))->create();

        $this->post(route('product.reviews.store', $product), ['name' => 'Buyer', 'rating' => 5, 'body' => 'A comfortable chair'])
            ->assertNotFound();
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_checkout_rejects_a_category_disabled_after_adding_to_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product))->assertRedirect();
        $product->category->update(['is_active' => false]);

        $this->post(route('checkout.store'), $this->checkoutDetails())->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_zero_sale_price_is_preserved_in_checkout(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'sale_price' => 0, 'stock' => 5]);
        $this->post(route('cart.add', $product))->assertRedirect();

        $this->post(route('checkout.store'), $this->checkoutDetails())->assertRedirectToRoute('dashboard');
        $this->assertDatabaseHas('orders', ['subtotal' => 0, 'total' => 80]);
        $this->assertSame(4, $product->fresh()->stock);
    }

    public function test_checkout_rejects_area_longer_than_database_column(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product))->assertRedirect();

        $this->post(route('checkout.store'), [...$this->checkoutDetails(), 'area' => str_repeat('a', 256)])
            ->assertSessionHasErrors('area');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_shop_rejects_array_search_input_instead_of_crashing(): void
    {
        $this->getJson(route('shop', ['q' => ['chair']]))->assertUnprocessable()->assertJsonValidationErrors('q');
    }

    public function test_failed_replacement_upload_keeps_the_previous_product_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/original.webp', 'original image');
        $product = Product::factory()->create(['image' => '/storage/products/original.webp']);
        $admin = User::factory()->admin()->create();
        $this->partialMock(ProductImageOptimizer::class, function ($mock): void {
            $mock->shouldReceive('store')->once()->andThrow(new RuntimeException('Storage failed'));
        });
        $image = UploadedFile::fake()->createWithContent('chair.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id, 'name' => $product->name, 'slug' => $product->slug,
            'price' => 1000, 'stock' => 5, 'image' => $image,
        ])->assertServerError();

        Storage::disk('public')->assertExists('products/original.webp');
        $this->assertSame('/storage/products/original.webp', $product->fresh()->image);
    }

    public function test_regular_order_status_endpoint_does_not_modify_fake_orders(): void
    {
        $order = $this->order(['is_fake' => true]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch(route('admin.order', $order), ['status' => 'বাতিল'])->assertNotFound();
        $this->assertSame('অর্ডার গ্রহণ', $order->fresh()->status);
    }

    public function test_fake_order_success_is_hidden_even_from_its_session(): void
    {
        $order = $this->order(['is_fake' => true]);

        $this->withSession(['customer_order_ids' => [$order->id]])->get(route('order.success', $order))->assertNotFound();
    }

    public function test_product_update_rejects_values_larger_than_database_columns(): void
    {
        $product = Product::factory()->create(['price' => 1000, 'stock' => 5]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id, 'name' => $product->name, 'slug' => $product->slug,
            'short_description' => str_repeat('a', 256), 'price' => 100000000, 'stock' => 4294967296,
        ])->assertSessionHasErrors(['short_description', 'price', 'stock']);

        $this->assertSame('1000.00', $product->fresh()->price);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_cart_update_rejects_invalid_quantity_without_changing_the_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect();

        $this->patch(route('cart.update', $product), ['quantity' => -1])->assertSessionHasErrors('quantity');
        $this->assertSame(2, session('cart')[$product->id]['quantity']);
    }

    public function test_successful_replacement_removes_the_old_image_after_saving(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/original.webp', 'original image');
        $product = Product::factory()->create(['image' => '/storage/products/original.webp']);
        $admin = User::factory()->admin()->create();
        $this->partialMock(ProductImageOptimizer::class, function ($mock): void {
            $mock->shouldReceive('store')->once()->andReturnUsing(function (): string {
                Storage::disk('public')->put('products/new.webp', 'new image');

                return '/storage/products/new.webp';
            });
        });
        $image = UploadedFile::fake()->createWithContent('chair.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id, 'name' => $product->name, 'slug' => $product->slug,
            'price' => 1000, 'stock' => 5, 'image' => $image,
        ])->assertRedirectToRoute('admin.products.index');

        Storage::disk('public')->assertExists('products/new.webp');
        Storage::disk('public')->assertMissing('products/original.webp');
        $this->assertSame('/storage/products/new.webp', $product->fresh()->image);
    }

    public function test_partial_gallery_upload_failure_cleans_up_new_files_and_keeps_old_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/original.webp', 'original image');
        $product = Product::factory()->create(['image' => '/storage/products/original.webp']);
        $admin = User::factory()->admin()->create();
        $this->partialMock(ProductImageOptimizer::class, function ($mock): void {
            $mock->shouldReceive('store')->once()->ordered()->andReturnUsing(function (): string {
                Storage::disk('public')->put('products/new.webp', 'new image');

                return '/storage/products/new.webp';
            });
            $mock->shouldReceive('store')->once()->ordered()->andThrow(new RuntimeException('Gallery storage failed'));
        });
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII=');

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id, 'name' => $product->name, 'slug' => $product->slug,
            'price' => 1000, 'stock' => 5, 'image' => UploadedFile::fake()->createWithContent('chair.png', $contents),
            'additional_images' => [UploadedFile::fake()->createWithContent('side.png', $contents)],
        ])->assertServerError();

        Storage::disk('public')->assertExists('products/original.webp');
        Storage::disk('public')->assertMissing('products/new.webp');
        $this->assertSame('/storage/products/original.webp', $product->fresh()->image);
    }

    public function test_failed_product_creation_cleans_up_uploaded_images(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->partialMock(ProductImageOptimizer::class, function ($mock): void {
            $mock->shouldReceive('store')->once()->ordered()->andReturnUsing(function (): string {
                Storage::disk('public')->put('products/new.webp', 'new image');

                return '/storage/products/new.webp';
            });
            $mock->shouldReceive('store')->once()->ordered()->andThrow(new RuntimeException('Gallery storage failed'));
        });
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII=');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'New chair', 'price' => 1000, 'stock' => 5,
            'image' => UploadedFile::fake()->createWithContent('chair.png', $contents),
            'additional_images' => [UploadedFile::fake()->createWithContent('side.png', $contents)],
        ])->assertServerError();

        Storage::disk('public')->assertMissing('products/new.webp');
        $this->assertDatabaseCount('products', 0);
    }

    /** @param array<string, mixed> $attributes */
    private function order(array $attributes = []): Order
    {
        return Order::create([
            'order_number' => 'CG-PRIVATE', 'name' => 'Buyer', 'phone' => '01700000000',
            'district' => 'Dhaka', 'area' => 'Dhanmondi', 'address' => 'House 10 Road 5',
            'payment_method' => 'cod', 'subtotal' => 1000, 'delivery_charge' => 80, 'total' => 1080,
            ...$attributes,
        ]);
    }

    /** @return array<string, string> */
    private function checkoutDetails(): array
    {
        return ['name' => 'Buyer', 'phone' => '01700000000', 'district' => 'Dhaka', 'area' => 'Dhanmondi', 'address' => 'House 10 Road 5', 'payment_method' => 'cod'];
    }
}
