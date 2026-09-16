<?php

namespace Tests\Feature;

use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_filters_show_only_matching_real_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $new = $this->createOrder('NEW-ORDER', 'অর্ডার গ্রহণ');
        $confirmed = $this->createOrder('CONFIRMED-ORDER', 'নিশ্চিত');
        $preparing = $this->createOrder('PREPARING-ORDER', 'প্রস্তুত');
        $shipped = $this->createOrder('SHIPPED-ORDER', 'পাঠানো');
        $completed = $this->createOrder('COMPLETED-ORDER', 'ডেলিভারি সম্পন্ন');
        $cancelled = $this->createOrder('CANCELLED-ORDER', 'বাতিল');
        $this->createOrder('FAKE-ORDER', 'অর্ডার গ্রহণ', true);

        $expectedOrders = [
            '' => [$new->id, $confirmed->id, $preparing->id, $shipped->id, $completed->id, $cancelled->id],
            'new' => [$new->id],
            'processing' => [$confirmed->id, $preparing->id, $shipped->id],
            'shipping' => [$shipped->id],
            'completed' => [$completed->id],
            'cancelled' => [$cancelled->id],
        ];

        foreach ($expectedOrders as $filter => $expectedIds) {
            $response = $this->actingAs($admin)->get(route('admin.orders.index', $filter === '' ? [] : ['filter' => $filter]));

            $response->assertOk()->assertDontSee('FAKE-ORDER');
            $this->assertSame($expectedIds, $response->viewData('orders')->pluck('id')->sort()->values()->all());
        }
    }

    public function test_order_list_paginates_and_keeps_the_selected_filter(): void
    {
        $admin = User::factory()->admin()->create();
        for ($index = 1; $index <= 21; $index++) {
            $this->createOrder('NEW-ORDER-'.$index, 'অর্ডার গ্রহণ');
        }
        $this->createOrder('OTHER-STATUS-ORDER', 'বাতিল');

        $firstPage = $this->actingAs($admin)->get(route('admin.orders.index', ['filter' => 'new']));
        $secondPage = $this->get(route('admin.orders.index', ['filter' => 'new', 'page' => 2]));

        $firstPage->assertOk()->assertDontSee('OTHER-STATUS-ORDER');
        $this->assertCount(20, $firstPage->viewData('orders'));
        $this->assertSame(21, $firstPage->viewData('orders')->total());
        $firstPage->assertSee(route('admin.orders.index', ['filter' => 'new', 'page' => 2]), false);
        $secondPage->assertOk()->assertDontSee('OTHER-STATUS-ORDER');
        $this->assertCount(1, $secondPage->viewData('orders'));
    }

    public function test_guest_is_redirected_to_login_from_order_lists(): void
    {
        $this->get(route('admin.orders.index'))->assertRedirectToRoute('login');
    }

    public function test_customer_cannot_open_order_lists(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.orders.index', ['filter' => 'processing']))
            ->assertForbidden();
    }

    public function test_unknown_order_filter_returns_not_found(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.orders.index', ['filter' => 'unknown']))
            ->assertNotFound();
    }

    public function test_admin_sidebar_links_to_all_order_lists(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin'));

        $response->assertSee('href="'.route('admin.orders.index').'"', false);
        foreach (['new', 'processing', 'shipping', 'completed', 'cancelled'] as $filter) {
            $response->assertSee('href="'.route('admin.orders.index', ['filter' => $filter]).'"', false);
        }
    }

    public function test_updating_order_status_moves_it_to_the_processing_list(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->createOrder('MOVING-ORDER', 'অর্ডার গ্রহণ');

        $this->actingAs($admin)
            ->from(route('admin.orders.index', ['filter' => 'new']))
            ->patch(route('admin.order', $order), ['status' => 'নিশ্চিত'])
            ->assertRedirectToRoute('admin.orders.index', ['filter' => 'new']);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'নিশ্চিত']);
        $this->get(route('admin.orders.index', ['filter' => 'new']))->assertDontSee('MOVING-ORDER');
        $this->get(route('admin.orders.index', ['filter' => 'processing']))->assertSee('MOVING-ORDER');
    }

    public function test_admin_can_view_incomplete_orders(): void
    {
        $admin = User::factory()->admin()->create();
        IncompleteOrder::query()->create(['session_id' => 'draft-session', 'name' => 'Draft Customer', 'quantity' => 2]);

        $this->actingAs($admin)->get(route('admin.incomplete-orders.index'))->assertOk()->assertSee('Draft Customer');
    }

    public function test_admin_can_create_and_view_a_fake_order_without_reducing_stock(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['price' => 5000, 'sale_price' => null, 'stock' => 10]);

        $this->actingAs($admin)->post(route('admin.fake-orders.store'), [
            'name' => 'Fake Customer',
            'phone' => '01700000000',
            'email' => 'fake@example.com',
            'district' => 'Dhaka',
            'area' => 'Dhanmondi',
            'address' => 'Test fake order address',
            'product_id' => $product->id,
            'quantity' => 2,
            'status' => 'অর্ডার গ্রহণ',
        ])->assertRedirectToRoute('admin.fake-orders.index');

        $this->assertDatabaseHas('orders', ['name' => 'Fake Customer', 'is_fake' => true, 'total' => 10000]);
        $this->assertSame(10, $product->fresh()->stock);

        $this->actingAs($admin)->get(route('admin.fake-orders.index'))->assertOk()->assertSee('Fake Customer')->assertSee($product->name);
    }

    public function test_admin_can_edit_order_details_without_changing_payment_or_totals(): void
    {
        $order = $this->createOrder('EDIT-ORDER', 'অর্ডার গ্রহণ');
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.orders.index'))
            ->assertSee(route('admin.orders.edit', $order), false)
            ->assertSee(route('admin.orders.destroy', $order), false);
        $this->get(route('admin.orders.edit', $order))->assertOk()->assertSee('EDIT-ORDER');

        $this->put(route('admin.orders.update', $order), [
            'name' => 'Updated Customer', 'phone' => '01812345678', 'email' => 'updated@example.com',
            'district' => 'Gazipur', 'area' => 'Tongi', 'address' => 'Updated address',
            'note' => 'Call before delivery', 'status' => 'নিশ্চিত',
            'total' => 1, 'payment_method' => 'paid', 'is_fake' => true,
        ])->assertRedirectToRoute('admin.orders.index');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id, 'name' => 'Updated Customer', 'phone' => '01812345678',
            'email' => 'updated@example.com', 'district' => 'Gazipur', 'area' => 'Tongi',
            'address' => 'Updated address', 'note' => 'Call before delivery', 'status' => 'নিশ্চিত',
            'total' => 2100, 'payment_method' => 'cod', 'is_fake' => false,
        ]);
    }

    public function test_invalid_order_details_are_rejected_without_changes(): void
    {
        $order = $this->createOrder('INVALID-EDIT', 'অর্ডার গ্রহণ');

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.orders.edit', $order))
            ->put(route('admin.orders.update', $order), [
                'name' => '', 'phone' => '123', 'email' => 'invalid', 'district' => '',
                'area' => '', 'address' => '', 'status' => 'invalid', 'note' => str_repeat('x', 2001),
            ])->assertRedirectToRoute('admin.orders.edit', $order)
            ->assertSessionHasErrors(['name', 'phone', 'email', 'district', 'area', 'address', 'status', 'note']);

        $this->assertSame('Order Customer', $order->fresh()->name);
        $this->assertSame('অর্ডার গ্রহণ', $order->fresh()->status);
    }

    public function test_admin_can_delete_an_order_and_its_items(): void
    {
        $order = $this->createOrder('DELETE-ORDER', 'অর্ডার গ্রহণ');
        $item = $order->items()->create(['product_name' => 'Chair', 'quantity' => 1, 'price' => 2000]);
        $other = $this->createOrder('KEEP-ORDER', 'অর্ডার গ্রহণ');

        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.orders.index'))
            ->delete(route('admin.orders.destroy', $order))->assertRedirectToRoute('admin.orders.index');

        $this->assertModelMissing($order);
        $this->assertModelMissing($item);
        $this->assertModelExists($other);
    }

    public function test_guests_and_customers_cannot_edit_or_delete_orders(): void
    {
        $order = $this->createOrder('PROTECTED-ORDER', 'অর্ডার গ্রহণ');

        $this->get(route('admin.orders.edit', $order))->assertRedirectToRoute('login');
        $this->put(route('admin.orders.update', $order), [])->assertRedirectToRoute('login');
        $this->delete(route('admin.orders.destroy', $order))->assertRedirectToRoute('login');
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.orders.edit', $order))->assertForbidden();
        $this->put(route('admin.orders.update', $order), [])->assertForbidden();
        $this->delete(route('admin.orders.destroy', $order))->assertForbidden();

        $this->assertModelExists($order);
    }

    public function test_real_order_actions_do_not_accept_fake_orders(): void
    {
        $order = $this->createOrder('FAKE-PROTECTED', 'অর্ডার গ্রহণ', true);
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.orders.edit', $order))->assertNotFound();
        $this->put(route('admin.orders.update', $order), $order->only(['name', 'phone', 'district', 'area', 'address', 'status']))->assertNotFound();
        $this->delete(route('admin.orders.destroy', $order))->assertNotFound();

        $this->assertModelExists($order);
    }

    private function createOrder(string $orderNumber, string $status, bool $isFake = false): Order
    {
        return Order::query()->create([
            'order_number' => $orderNumber,
            'name' => 'Order Customer',
            'phone' => '01700000000',
            'district' => 'Dhaka',
            'area' => 'Dhanmondi',
            'address' => 'House 10, Road 5',
            'payment_method' => 'cod',
            'subtotal' => 2000,
            'delivery_charge' => 100,
            'total' => 2100,
            'status' => $status,
            'is_fake' => $isFake,
        ]);
    }
}
