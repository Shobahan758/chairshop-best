<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_login_page(): void
    {
        $this->get(route('login'))
            ->assertSee('আপনার অ্যাকাউন্টে লগইন করুন');
    }

    public function test_customer_login_redirects_to_user_dashboard(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret-password']);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
