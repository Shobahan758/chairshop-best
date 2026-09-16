<?php

namespace Tests\Feature;

use App\Models\CustomerAccount;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_tabs_and_invoice_use_real_order_data(): void
    {
        $user = User::factory()->create();
        $account = CustomerAccount::factory()->create(['user_id' => $user->id]);
        $order = $this->createOrder($account);
        GeneralSetting::create(['store_name' => 'Test Chair Store', 'support_phone' => '01799999999']);

        $this->actingAs($user);
        foreach (['summary' => 'Test Chair', 'vendor' => 'Test Chair Store', 'delivery' => 'Delivery information', 'reviews' => 'Write a review', 'track' => 'Order Placed'] as $tab => $text) {
            $this->get(route('dashboard', ['section' => 'orders', 'order' => $order->id, 'tab' => $tab]))
                ->assertOk()->assertSee($text)->assertSee('Back to Orders');
        }
        $product = Product::findOrFail($order->items->first()->product_id);
        $this->get(route('product', ['product' => $product, 'tab' => 'reviews']))->assertOk()
            ->assertSee('tab-pane fade show active" id="productReviews"', false);
        $this->get(route('customer.orders.invoice', $order))->assertOk()
            ->assertSee($order->order_number)->assertSee('Test Chair')->assertSee('1,080.00')->assertSee('Print / Save PDF');
    }

    public function test_customer_cannot_read_another_customers_order_or_invoice(): void
    {
        $order = $this->createOrder(CustomerAccount::factory()->create());
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard', ['section' => 'orders', 'order' => $order->id]))->assertNotFound();
        $this->get(route('customer.orders.invoice', $order))->assertNotFound();
    }

    public function test_guest_can_only_access_orders_in_their_account_session(): void
    {
        $account = CustomerAccount::factory()->create();
        $own = $this->createOrder($account);
        $other = $this->createOrder(CustomerAccount::factory()->create());
        $this->withSession(['customer_account_id' => $account->id]);

        $this->get(route('dashboard', ['section' => 'orders', 'order' => $own->id]))->assertOk();
        $this->get(route('customer.orders.invoice', $own))->assertOk();
        $this->get(route('customer.orders.invoice', $other))->assertNotFound();
    }

    public function test_admin_delivery_and_status_changes_appear_in_customer_timeline(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder(CustomerAccount::factory()->create(['user_id' => $user->id]));
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.orders.update', $order), [
            'name' => $order->name, 'phone' => $order->phone, 'district' => $order->district,
            'area' => $order->area, 'address' => $order->address, 'status' => 'পাঠানো',
            'delivery_name' => 'Test Courier', 'delivery_phone' => '01800000000',
            'courier_tracking_url' => 'https://example.com/track/123',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $order->refresh();
        $this->assertSame(['অর্ডার গ্রহণ', 'পাঠানো'], array_column($order->status_history, 'status'));
        $this->actingAs($user)->get(route('dashboard', ['section' => 'orders', 'order' => $order->id, 'tab' => 'delivery']))
            ->assertOk()->assertSee('Test Courier')->assertSee('01800000000')->assertSee('https://example.com/track/123');
        $this->get(route('dashboard', ['section' => 'orders', 'order' => $order->id, 'tab' => 'track']))
            ->assertOk()->assertSee('aria-current="step"', false)->assertSee('Order is on the way');
    }

    public function test_cancelled_order_and_invalid_tabs_are_handled(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder(CustomerAccount::factory()->create(['user_id' => $user->id]));
        $order->update(['status' => 'বাতিল']);

        $this->actingAs($user)->get(route('dashboard', ['section' => 'orders', 'order' => $order->id]))
            ->assertOk()->assertSee('Order cancelled')->assertDontSee('aria-current="step"', false);
        $this->get(route('dashboard', ['section' => 'orders', 'order' => $order->id, 'tab' => 'unknown']))->assertNotFound();
    }

    private function createOrder(CustomerAccount $account): Order
    {
        $product = Product::factory()->create();
        $order = new Order([
            'order_number' => 'TEST-'.$account->id, 'name' => 'Customer', 'phone' => '01700000000',
            'district' => 'Dhaka', 'area' => 'Dhanmondi', 'address' => 'House 10, Road 5',
            'payment_method' => 'cod', 'subtotal' => 1000, 'delivery_charge' => 80, 'total' => 1080,
        ]);
        $order->customer_account_id = $account->id;
        $order->save();
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Test Chair', 'quantity' => 1, 'price' => 1000]);

        return $order;
    }
}
