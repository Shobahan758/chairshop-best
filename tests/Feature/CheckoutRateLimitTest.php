<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_background_requests_do_not_consume_the_order_submission_limit(): void
    {
        for ($attempt = 0; $attempt < 12; $attempt++) {
            $this->postJson(route('tracking.events'), [])->assertUnprocessable();
            $this->postJson(route('checkout.incomplete.store'), [])->assertUnprocessable();
        }

        $product = Product::factory()->create(['stock' => 3]);
        $this->withSession(['cart' => [$product->id => [
            'id' => $product->id, 'name' => $product->name, 'image' => $product->image,
            'price' => $product->current_price, 'quantity' => 1,
        ]]])->post(route('checkout.store'), $this->checkoutData())->assertRedirectToRoute('dashboard');
        $this->assertDatabaseCount('orders', 1);
        $this->get(route('dashboard'))->assertOk()->assertSee(Order::firstOrFail()->order_number);
    }

    public function test_excessive_submissions_keep_the_customer_on_checkout_with_their_input(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('checkout.store'), $this->checkoutData())->assertRedirectToRoute('cart');
        }

        $this->post(route('checkout.store'), $this->checkoutData())
            ->assertRedirectToRoute('checkout')
            ->assertSessionHasErrors('checkout')
            ->assertSessionHasInput('phone', '01800000000')
            ->assertHeader('Retry-After');
        $this->postJson(route('checkout.store'), $this->checkoutData())->assertStatus(429);
        $this->assertDatabaseCount('orders', 0);
    }

    private function checkoutData(): array
    {
        return [
            'name' => 'Test Buyer', 'phone' => '01800000000', 'district' => 'Dhaka',
            'area' => 'Dhanmondi', 'address' => 'House 10, Road 5', 'payment_method' => 'cod',
        ];
    }
}
