<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_customer_can_open_user_dashboard(): void
    {
        $user = User::factory()->create();
        Order::query()->create([
            'order_number' => 'CG-TEST-1001',
            'name' => $user->name,
            'phone' => '01700000000',
            'email' => $user->email,
            'district' => 'ঢাকা',
            'area' => 'ধানমন্ডি',
            'address' => 'বাড়ি ১০, রোড ৫, ধানমন্ডি, ঢাকা',
            'payment_method' => 'cod',
            'subtotal' => 2000,
            'delivery_charge' => 100,
            'total' => 2100,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee($user->name)
            ->assertSee('আমার অর্ডার')
            ->assertSee('CG-TEST-1001');
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin'));
    }
}
