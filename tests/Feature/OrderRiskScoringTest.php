<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderRiskScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderRiskScoringTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('riskCases')]
    public function test_risk_rules_have_the_requested_weights(bool $samePhone, bool $sameIp, int $secondsAgo, bool $changedName, bool $wasFake, array $reasons): void
    {
        $this->freezeTime();
        $this->previousOrder([
            'phone' => $samePhone ? '01700000000' : '01800000000',
            'source_ip' => $sameIp ? '203.0.113.8' : '203.0.113.9',
            'created_at' => now()->subSeconds($secondsAgo),
            'is_fake' => $wasFake,
        ]);

        $result = (new OrderRiskScorer)->assess('01700000000', $changedName ? 'Different Customer' : 'Customer', 'House 10, Road 5', 'Dhanmondi', 'Dhaka', '203.0.113.8');

        $this->assertSame($reasons, $result['risk_reasons']);
        $this->assertSame(array_sum($reasons), $result['risk_score']);
        $this->assertSame(array_sum($reasons) >= 60, $result['is_fake']);
    }

    public static function riskCases(): array
    {
        return [
            'unrelated history' => [false, false, 86400, false, false, []],
            'repeat phone' => [true, false, 86400, false, false, ['repeated_phone' => 40]],
            'same IP at thirty minutes' => [false, true, 1800, false, false, ['repeated_ip' => 25]],
            'IP outside window' => [false, true, 1801, false, false, []],
            'phone at two minutes' => [true, false, 120, false, false, ['repeated_phone' => 40, 'rapid_repeat' => 15]],
            'phone outside fast window' => [true, false, 121, false, false, ['repeated_phone' => 40]],
            'changed name' => [true, false, 86400, true, false, ['repeated_phone' => 40, 'changed_details' => 20]],
            'previous fake phone' => [true, false, 86400, false, true, ['repeated_phone' => 40, 'previous_fake_phone' => 100]],
            'all signals' => [true, true, 60, true, true, ['repeated_phone' => 40, 'repeated_ip' => 25, 'rapid_repeat' => 15, 'changed_details' => 20, 'previous_fake_phone' => 100]],
        ];
    }

    public function test_changed_address_counts_once_and_whitespace_does_not_add_risk(): void
    {
        $this->previousOrder(['created_at' => now()->subDay(), 'source_ip' => null]);
        $scorer = new OrderRiskScorer;

        $unchanged = $scorer->assess('01700000000', ' CUSTOMER ', 'House 10,  Road 5', 'Dhanmondi', 'Dhaka', null);
        $changed = $scorer->assess('01700000000', 'Customer', 'House 22, Road 8', 'Mirpur', 'Dhaka', null);

        $this->assertSame(40, $unchanged['risk_score']);
        $this->assertSame(['repeated_phone' => 40, 'changed_details' => 20], $changed['risk_reasons']);
    }

    public function test_checkout_saves_server_risk_and_suspicious_order_appears_in_fake_list(): void
    {
        $this->previousOrder(['created_at' => now()->subMinute(), 'is_fake' => true]);
        $product = Product::factory()->create(['price' => 1000, 'sale_price' => null, 'stock' => 3]);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.8'])
            ->withSession(['cart' => [$product->id => ['id' => $product->id, 'name' => $product->name, 'image' => $product->image, 'price' => 1000, 'quantity' => 1]]])
            ->post(route('checkout.store'), [
                'name' => 'Changed Customer', 'phone' => '01700000000', 'district' => 'Dhaka', 'area' => 'Dhanmondi',
                'address' => 'House 10, Road 5', 'payment_method' => 'cod',
                'risk_score' => 0, 'is_fake' => false, 'source_ip' => '192.0.2.1',
            ])->assertSessionHasNoErrors()->assertRedirect();

        $order = Order::where('name', 'Changed Customer')->firstOrFail();
        $this->assertSame(200, $order->risk_score);
        $this->assertTrue($order->is_fake);
        $this->assertSame('203.0.113.8', $order->source_ip);
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.fake-orders.index'))
            ->assertOk()->assertSee($order->order_number)->assertSee('Risk: 200')->assertSee('কারণ দেখুন');
        $this->get(route('admin.fake-orders.today'))->assertOk()->assertSee($order->order_number);
    }

    /** @param array<string, mixed> $attributes */
    private function previousOrder(array $attributes = []): Order
    {
        $order = new Order([
            'order_number' => 'PRIOR-ORDER', 'name' => 'Customer', 'phone' => '01700000000',
            'district' => 'Dhaka', 'area' => 'Dhanmondi', 'address' => 'House 10, Road 5',
            'payment_method' => 'cod', 'subtotal' => 1000, 'delivery_charge' => 80, 'total' => 1080,
            'source_ip' => '203.0.113.8',
        ]);
        $order->forceFill($attributes)->save();

        return $order;
    }
}
