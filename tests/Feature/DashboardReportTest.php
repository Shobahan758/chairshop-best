<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_separates_delivered_pending_cancelled_and_fake_values(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 9)->startOfDay());
        $this->order('DELIVERED', 'ডেলিভারি সম্পন্ন', 1000);
        $this->order('PENDING', 'অর্ডার গ্রহণ', 500);
        $this->order('CANCELLED', 'বাতিল', 800);
        $this->order('FAKE', 'অর্ডার গ্রহণ', 900, true);
        $this->travelBack();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin', ['from' => '2026-09-08', 'to' => '2026-09-09']));
        $response->assertOk()->assertViewHas('orderCount', 3)
            ->assertViewHas('orderValue', 1500.0)->assertViewHas('deliveredValue', 1000.0)
            ->assertViewHas('pendingValue', 500.0)->assertViewHas('cancelledValue', 800.0)
            ->assertViewHas('fakeCount', 1)->assertViewHas('discountValue', 100.0)
            ->assertViewHas('deliveryValue', 160.0)
            ->assertViewHas('series', fn ($series) => count($series) === 2 && $series[0]['orders'] === 0 && $series[1]['value'] === 1500.0)
            ->assertViewHas('topProducts', fn ($products) => (int) $products->sum('units') === 2)
            ->assertDontSee('href="#"', false);
        $this->get(route('admin', ['from' => '2026-09-10', 'to' => '2026-09-10']))
            ->assertOk()->assertViewHas('orderCount', 0)->assertViewHas('orderValue', 0.0);
    }

    public function test_inventory_is_current_and_event_names_match_actual_tracking(): void
    {
        Product::factory()->create(['stock' => 0]);
        Product::factory()->create(['stock' => 3]);
        Product::factory()->create(['stock' => 20]);
        TrackingEvent::create(['event' => 'checkout_open', 'path' => '/checkout', 'session_hash' => 'session-one']);
        TrackingEvent::create(['event' => 'page_view', 'path' => '/', 'session_hash' => 'session-one']);
        $this->actingAs(User::factory()->admin()->create())->get(route('admin'))
            ->assertOk()->assertViewHas('outOfStock', 1)->assertViewHas('lowStockCount', 1)
            ->assertViewHas('stockUnits', 23)->assertViewHas('trackedSessions', 1)
            ->assertViewHas('eventCounts', fn ($counts) => (int) $counts->get('checkout_open') === 1);
    }

    public function test_report_permission_does_not_grant_management_actions_and_invalid_dates_are_rejected(): void
    {
        $staff = User::factory()->create(['is_admin' => true, 'role' => 'manager', 'permissions' => ['dashboard']]);
        $this->actingAs($staff)->get(route('admin'))->assertOk()
            ->assertDontSee('href="'.route('admin.products.create').'"', false);
        $this->put(route('admin.settings.general.update'), [])->assertForbidden();
        $this->get(route('admin', ['from' => '2026-09-10', 'to' => '2026-09-09']))->assertSessionHasErrors('to');
        $this->get(route('admin', ['from' => '2020-01-01', 'to' => '2026-01-01']))->assertSessionHasErrors('from');
        $staff->update(['permissions' => []]);
        $this->actingAs($staff->fresh())->get(route('admin'))->assertOk()->assertDontSee('Order value over time');
    }

    private function order(string $number, string $status, int $total, bool $fake = false): void
    {
        $order = Order::create([
            'order_number' => $number, 'status' => $status, 'is_fake' => $fake,
            'name' => 'Buyer', 'phone' => '01800000000', 'district' => 'Dhaka',
            'area' => 'Dhanmondi', 'address' => 'House 10, Road 5', 'payment_method' => 'cod',
            'subtotal' => $total - 30, 'discount' => 50, 'delivery_charge' => 80, 'total' => $total,
        ]);
        $product = Product::factory()->create();
        $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => 1, 'price' => $total - 30]);
    }
}
