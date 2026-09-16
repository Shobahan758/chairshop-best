<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_product_to_cart_records_the_tracking_event(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect();

        $this->assertDatabaseHas('tracking_events', ['event' => 'add_to_cart']);
    }

    public function test_customer_can_increase_and_decrease_cart_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 5, 'price' => 1000, 'sale_price' => null]);
        $cart = [$product->id => $this->cartItem($product, 1)];

        $this->withSession(['cart' => $cart])->patch(route('cart.update', $product), ['quantity' => 2])->assertRedirect();

        $this->assertSame(2, session('cart')[$product->id]['quantity']);
    }

    public function test_valid_coupon_can_be_applied_to_checkout(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null]);
        Coupon::query()->create(['code' => 'CHAIR10', 'discount_type' => 'percent', 'value' => 10, 'minimum_order' => 1000]);

        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.coupon.apply'), ['coupon_code' => 'chair10'])
            ->assertRedirect()
            ->assertSessionHas('coupon_code', 'CHAIR10');
    }

    public function test_applied_coupon_is_visible_in_cart_summary(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null]);
        Coupon::query()->create(['code' => 'CHAIR10', 'discount_type' => 'percent', 'value' => 10, 'minimum_order' => 1000]);

        $this->withSession([
            'cart' => [$product->id => $this->cartItem($product, 1)],
            'coupon_code' => 'CHAIR10',
        ])->get(route('cart'))
            ->assertOk()
            ->assertSee('CHAIR10')
            ->assertSee('কুপন ছাড়');
    }

    public function test_invalid_coupon_is_rejected(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null]);

        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->from(route('checkout'))
            ->post(route('checkout.coupon.apply'), ['coupon_code' => 'NOTREAL'])
            ->assertRedirect(route('checkout'))
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_coupon_discount_is_recalculated_and_saved_with_the_order(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null, 'stock' => 3]);
        Coupon::query()->create(['code' => 'SAVE500', 'discount_type' => 'fixed', 'value' => 500, 'minimum_order' => 1000]);

        $this->withSession([
            'cart' => [$product->id => $this->cartItem($product, 2)],
            'coupon_code' => 'SAVE500',
        ])->post(route('checkout.store'), [
            'name' => 'Test Customer',
            'phone' => '01700000000',
            'email' => 'customer@example.com',
            'district' => 'ঢাকা',
            'area' => 'ধানমন্ডি',
            'address' => 'বাড়ি ১০, রোড ৫, ধানমন্ডি, ঢাকা',
            'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame('4000.00', $order->subtotal);
        $this->assertSame('500.00', $order->discount);
        $this->assertSame('SAVE500', $order->coupon_code);
        $this->assertSame('3580.00', $order->total);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('tracking_events', ['event' => 'purchase_complete']);
    }

    public function test_mobile_payment_requires_sender_phone_and_transaction_id(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null]);

        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.store'), [
                'name' => 'Test Customer',
                'phone' => '01700000000',
                'district' => 'ঢাকা',
                'area' => 'ধানমন্ডি',
                'address' => 'বাড়ি ১০, রোড ৫, ধানমন্ডি, ঢাকা',
                'payment_method' => 'bkash',
            ])->assertSessionHasErrors(['payment_phone', 'transaction_id']);
    }

    public function test_dhaka_delivery_costs_eighty_taka_regardless_of_client_charge(): void
    {
        foreach (['ঢাকা', ' DhAkA ', 'dacca'] as $district) {
            $product = Product::factory()->create(['price' => 2000, 'sale_price' => null, 'stock' => 2]);

            $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
                ->post(route('checkout.store'), [
                    'name' => 'Test Customer', 'phone' => '01700000000', 'district' => $district,
                    'area' => 'Dhanmondi', 'address' => 'A complete test delivery address',
                    'payment_method' => 'cod', 'delivery_charge' => 0,
                ])->assertSessionHasNoErrors()->assertRedirect();

            $this->assertDatabaseHas('orders', ['district' => trim($district), 'delivery_charge' => 80, 'total' => 2080]);
        }
    }

    public function test_checkout_accepts_a_manually_typed_district_without_a_list(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null, 'stock' => 2]);

        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.store'), [
                'name' => 'Test Customer',
                'phone' => '01700000000',
                'district' => 'bogura',
                'area' => 'shodur',
                'address' => 'A complete test delivery address',
                'payment_method' => 'cod',
            ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'district' => 'bogura',
            'area' => 'shodur',
            'delivery_charge' => 120,
        ]);
    }

    public function test_logged_in_customer_is_redirected_to_dashboard_after_ordering(): void
    {
        $customer = User::factory()->create(['email' => 'account@example.com']);
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null, 'stock' => 2]);

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.store'), [
                'name' => 'Test Customer',
                'phone' => '01700000000',
                'email' => 'different@example.com',
                'district' => 'ঢাকা',
                'area' => 'ধানমন্ডি',
                'address' => 'বাড়ি ১০, রোড ৫, ধানমন্ডি, ঢাকা',
                'payment_method' => 'cod',
            ])->assertRedirectToRoute('dashboard');

        $this->assertDatabaseHas('orders', [
            'email' => 'account@example.com',
            'phone' => '01700000000',
        ]);

        $this->get(route('dashboard'))->assertSee(Order::firstOrFail()->order_number);
    }

    public function test_guest_dashboard_shows_only_orders_placed_in_the_current_session(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.store'), [
                'name' => 'Guest Customer',
                'phone' => '01700000000',
                'district' => 'Dhaka',
                'area' => 'Dhanmondi',
                'address' => 'House 10, Road 5, Dhanmondi',
                'payment_method' => 'cod',
            ])->assertRedirectToRoute('dashboard')
            ->assertSessionMissing('cart');

        $order = Order::firstOrFail();
        $otherOrder = $order->replicate();
        $otherOrder->order_number = 'CG-OTHER-CUSTOMER';
        $otherOrder->customer_account_id = null;
        $otherOrder->save();

        $this->assertGuest();
        $this->get(route('dashboard'))
            ->assertSee('Guest Customer')
            ->assertSee($order->order_number)
            ->assertDontSee($otherOrder->order_number)
            ->assertDontSee('লগআউট');

        $this->flushSession();
        $this->get(route('dashboard'))->assertRedirectToRoute('login');
    }

    public function test_checkout_with_an_empty_cart_redirects_without_creating_an_order(): void
    {
        $this->post(route('checkout.store'), [
            'name' => 'Guest Customer',
            'phone' => '01700000000',
            'district' => 'Dhaka',
            'area' => 'Dhanmondi',
            'address' => 'House 10, Road 5, Dhanmondi',
            'payment_method' => 'cod',
        ])->assertRedirectToRoute('cart')
            ->assertSessionHas('success', 'চেকআউটের আগে কার্টে পণ্য যোগ করুন');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_resubmitting_a_completed_checkout_returns_to_dashboard_without_another_order(): void
    {
        $product = Product::factory()->create(['stock' => 3]);
        $details = [
            'name' => 'Guest Customer',
            'phone' => '01700000000',
            'district' => 'Dhaka',
            'area' => 'Dhanmondi',
            'address' => 'House 10, Road 5, Dhanmondi',
            'payment_method' => 'cod',
        ];

        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.store'), $details)->assertRedirectToRoute('dashboard');
        $this->post(route('checkout.store'), $details)->assertRedirectToRoute('dashboard');

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(2, $product->fresh()->stock);
        $this->get(route('dashboard'))->assertSee(Order::firstOrFail()->order_number);
    }

    public function test_mobile_payment_details_are_saved_with_the_order(): void
    {
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null, 'stock' => 2]);

        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.store'), [
                'name' => 'Test Customer',
                'phone' => '01700000000',
                'district' => 'ঢাকা',
                'area' => 'ধানমন্ডি',
                'address' => 'বাড়ি ১০, রোড ৫, ধানমন্ডি, ঢাকা',
                'payment_method' => 'nagad',
                'payment_phone' => '01800000000',
                'transaction_id' => '8N7A6BC12D',
            ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'payment_method' => 'nagad',
            'payment_phone' => '01800000000',
            'transaction_id' => '8N7A6BC12D',
        ]);
    }

    /** @return array<string, mixed> */
    public function test_admin_storefront_checkout_opens_an_isolated_customer_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Store Admin']);
        $product = Product::factory()->create(['stock' => 5]);
        $this->actingAs($admin)->get(route('dashboard'))->assertRedirectToRoute('admin');
        $this->withSession(['cart' => [$product->id => $this->cartItem($product, 1)]])
            ->post(route('checkout.store'), [
                'name' => 'Storefront Buyer', 'phone' => '01800000000',
                'district' => 'Dhaka', 'area' => 'Dhanmondi',
                'address' => 'House 10, Road 5', 'payment_method' => 'cod',
            ])->assertRedirectToRoute('dashboard');

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('customer_accounts', ['id' => $order->customer_account_id, 'user_id' => null, 'name' => 'Storefront Buyer']);
        $this->get(route('dashboard'))->assertOk()->assertSee($order->order_number);
        $this->get(route('dashboard', ['section' => 'orders', 'order' => $order->id]))->assertOk();
        $this->get(route('customer.orders.invoice', $order))->assertOk();
        $this->get(route('dashboard', ['section' => 'profile']))->assertSee('Storefront Buyer');
        $this->put(route('customer.profile'), ['name' => 'Buyer Updated', 'phone' => '01800000000'])->assertRedirect();
        $this->assertSame('Store Admin', $admin->fresh()->name);
        $otherOrder = $order->replicate();
        $otherOrder->order_number = 'CG-PRIVATE-ORDER';
        $otherOrder->customer_account_id = null;
        $otherOrder->email = $admin->email;
        $otherOrder->save();
        $this->get(route('dashboard'))->assertDontSee($otherOrder->order_number);
        $this->get(route('dashboard', ['section' => 'orders', 'order' => $otherOrder->id]))->assertNotFound();
        $this->get(route('customer.orders.invoice', $otherOrder))->assertNotFound();
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin'))->assertOk();
        $this->post(route('customer.tickets.store'), ['subject' => 'Delivery question', 'message' => 'Please confirm delivery'])
            ->assertRedirectToRoute('dashboard', ['section' => 'support']);
        $this->get(route('dashboard', ['section' => 'support']))->assertSee('Delivery question');

        $this->flushSession();
        $this->get(route('dashboard'))->assertRedirectToRoute('admin');
        $this->get(route('customer.orders.invoice', $order))->assertForbidden();
    }

    private function cartItem(Product $product, int $quantity): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->image,
            'price' => (float) $product->current_price,
            'quantity' => $quantity,
        ];
    }
}
