<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin'))->assertRedirect(route('login'));
    }

    public function test_customer_is_forbidden_from_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin'))->assertForbidden();
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin'))
            ->assertSee('Business Overview');
    }
}
