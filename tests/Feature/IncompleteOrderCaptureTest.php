<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncompleteOrderCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_details_are_saved_as_an_incomplete_order(): void
    {
        $product = Product::factory()->create();
        $cart = [$product->id => ['id' => $product->id, 'name' => $product->name, 'quantity' => 2, 'price' => 1000]];

        $this->withSession(['cart' => $cart])->postJson(route('checkout.incomplete.store'), [
            'name' => 'Draft Customer',
            'phone' => '01700000000',
            'address' => 'Draft address',
        ])->assertOk()->assertJson(['saved' => true]);

        $this->assertDatabaseHas('incomplete_orders', ['name' => 'Draft Customer', 'phone' => '01700000000', 'quantity' => 2]);
    }

    public function test_name_and_valid_phone_are_required_before_capture(): void
    {
        $product = Product::factory()->create();
        $this->withSession(['cart' => [$product->id => ['id' => $product->id, 'quantity' => 1]]]);

        $this->postJson(route('checkout.incomplete.store'), ['name' => 'Customer'])
            ->assertJsonValidationErrors('phone');
        $this->postJson(route('checkout.incomplete.store'), ['phone' => '01700000000'])
            ->assertJsonValidationErrors('name');
        $this->postJson(route('checkout.incomplete.store'), ['name' => 'Customer', 'phone' => '017'])
            ->assertJsonValidationErrors('phone');

        $this->assertDatabaseCount('incomplete_orders', 0);
    }

    public function test_later_details_update_the_same_incomplete_order(): void
    {
        $product = Product::factory()->create();
        $this->withSession(['cart' => [$product->id => ['id' => $product->id, 'quantity' => 1]]]);
        $this->withCredentials()->withCookie(config('session.cookie'), session()->getId());
        $details = ['name' => 'Draft Customer', 'phone' => '01700000000'];

        $this->postJson(route('checkout.incomplete.store'), $details)->assertOk();
        $this->postJson(route('checkout.incomplete.store'), [...$details, 'district' => 'Dhaka'])
            ->assertOk();

        $this->assertDatabaseCount('incomplete_orders', 1);
        $this->assertDatabaseHas('incomplete_orders', [...$details, 'district' => 'Dhaka']);
    }

    public function test_completed_checkout_removes_draft_and_rejects_late_capture(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'price' => 1000, 'sale_price' => null]);
        $this->withSession(['cart' => [$product->id => [
            'id' => $product->id, 'name' => $product->name, 'slug' => $product->slug,
            'image' => $product->image, 'quantity' => 1, 'price' => 1000,
        ]]]);
        $this->withCredentials()->withCookie(config('session.cookie'), session()->getId());
        $details = ['name' => 'Draft Customer', 'phone' => '01700000000'];
        $this->postJson(route('checkout.incomplete.store'), $details)->assertOk();

        $this->post(route('checkout.store'), [...$details, 'district' => 'Dhaka', 'area' => 'Dhanmondi',
            'address' => 'A complete delivery address', 'payment_method' => 'cod',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('incomplete_orders', 0);
        $this->postJson(route('checkout.incomplete.store'), $details)->assertUnprocessable();
        $this->assertDatabaseCount('incomplete_orders', 0);
    }
}
