<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_each_staff_role_and_the_password_is_hashed(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.settings.users'))->assertOk()->assertSee('Add user');
        $this->get(route('admin.settings.users.create'))->assertOk();
        foreach (['super_admin', 'admin', 'manager'] as $role) {
            $this->post(route('admin.settings.users.store'), $this->staffData($role))->assertRedirectToRoute('admin.settings.users');
            $staff = User::where('email', $role.'@example.com')->firstOrFail();
            $this->assertTrue($staff->is_admin);
            $this->assertSame($role, $staff->role);
            $this->assertTrue(Hash::check('secure-password', $staff->password));
        }
    }

    public function test_only_super_admin_can_manage_staff_even_with_direct_requests(): void
    {
        $target = User::factory()->admin()->create();
        foreach (['admin', 'manager', 'customer'] as $role) {
            $actor = User::factory()->create(['is_admin' => $role !== 'customer', 'role' => $role]);
            $this->actingAs($actor);
            $this->get(route('admin.settings.users'))->assertForbidden();
            $this->get(route('admin.settings.users.create'))->assertForbidden();
            $this->get(route('admin.settings.users.edit', $target))->assertForbidden();
            $this->post(route('admin.settings.users.store'), $this->staffData('super_admin'))->assertForbidden();
            $this->put(route('admin.settings.users.update', $target), $this->staffData('manager'))->assertForbidden();
        }
        $this->assertSame('super_admin', $target->fresh()->role);
        $this->assertDatabaseMissing('users', ['email' => 'super_admin@example.com']);
    }

    public function test_admin_and_manager_routes_and_menus_match_their_permissions(): void
    {
        $manager = User::factory()->create(['is_admin' => true, 'role' => 'manager']);
        $this->actingAs($manager)->get(route('admin'))->assertOk()
            ->assertSee('Manager')->assertDontSee('href="'.route('admin.settings.users').'"', false)
            ->assertDontSee('href="'.route('admin.tracking').'"', false);
        foreach (['admin.products.index', 'admin.orders.index', 'admin.settings.profile'] as $route) {
            $this->get(route($route))->assertOk();
        }
        foreach (['admin.settings.general', 'admin.settings.site', 'admin.tracking', 'admin.support.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->put(route('admin.settings.general.update'), [])->assertForbidden();
        $this->put(route('admin.tracking.update'), [])->assertForbidden();
        $this->put(route('admin.settings.profile.update'), [
            'name' => 'Manager Updated', 'email' => $manager->email, 'role' => 'super_admin', 'is_admin' => true,
        ])->assertRedirect();
        $this->assertSame('manager', $manager->fresh()->role);

        $this->actingAs(User::factory()->create(['is_admin' => true, 'role' => 'admin']));
        foreach (['admin.settings.general', 'admin.settings.site', 'admin.tracking', 'admin.support.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_staff_can_log_in_and_reach_their_dashboard(): void
    {
        User::factory()->create(['email' => 'manager@example.com', 'password' => 'secure-password', 'is_admin' => true, 'role' => 'manager']);
        $this->post(route('login'), ['email' => 'manager@example.com', 'password' => 'secure-password'])->assertRedirectToRoute('admin');
        $this->get(route('admin'))->assertOk()->assertSee('Manager');
    }

    public function test_staff_updates_preserve_blank_password_and_cannot_remove_own_super_admin_access(): void
    {
        $owner = User::factory()->admin()->create();
        $staff = User::factory()->create(['role' => 'manager', 'is_admin' => true]);
        $password = $staff->password;
        $this->actingAs($owner)->put(route('admin.settings.users.update', $staff), [
            ...$this->staffData('admin'), 'password' => '', 'password_confirmation' => '',
        ])->assertRedirectToRoute('admin.settings.users');
        $this->assertSame('admin', $staff->fresh()->role);
        $this->assertSame($password, $staff->fresh()->password);
        $this->put(route('admin.settings.users.update', $owner), [
            ...$this->staffData('manager'), 'email' => $owner->email,
        ])->assertSessionHasErrors('role');
        $this->assertTrue($owner->fresh()->isSuperAdmin());
    }

    public function test_invalid_role_duplicate_email_and_unconfirmed_password_do_not_create_users(): void
    {
        $owner = User::factory()->admin()->create();
        $this->actingAs($owner)->post(route('admin.settings.users.store'), [
            ...$this->staffData('invalid'), 'email' => $owner->email, 'password_confirmation' => 'wrong',
        ])->assertSessionHasErrors(['role', 'email', 'password']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_selected_access_is_saved_and_enforced_on_pages_actions_and_menus(): void
    {
        $owner = User::factory()->admin()->create();
        $this->actingAs($owner)->post(route('admin.settings.users.store'), [
            ...$this->staffData('manager'), 'permissions' => ['products', 'tracking'],
        ])->assertRedirectToRoute('admin.settings.users');
        $staff = User::where('email', 'manager@example.com')->firstOrFail();
        $this->assertSame(['products', 'tracking'], $staff->permissions);
        $this->get(route('admin.settings.users.edit', $staff))->assertOk()->assertSee('Select access');

        $this->actingAs($staff)->get(route('admin'))->assertOk()->assertDontSee('Business Overview')
            ->assertSee('href="'.route('admin.products.index').'"', false)
            ->assertDontSee('href="'.route('admin.orders.index').'"', false);
        $this->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.tracking'))->assertOk();
        $this->get(route('admin.orders.index'))->assertForbidden();
        $this->get(route('admin.categories.index'))->assertForbidden();
        $this->post(route('admin.categories.store'), [])->assertForbidden();
        $this->put(route('admin.settings.general.update'), [])->assertForbidden();
        $this->post(route('admin.settings.users.store'), [
            ...$this->staffData('super_admin'), 'permissions' => array_keys(config('staff_permissions')),
        ])->assertForbidden();

        $this->actingAs($owner)->put(route('admin.settings.users.update', $staff), [
            ...$this->staffData('manager'), 'permissions' => ['orders'],
        ])->assertRedirect();
        $this->actingAs($staff->fresh())->get(route('admin.products.index'))->assertForbidden();
        $this->get(route('admin.orders.index'))->assertOk();
        $this->get(route('admin.tracking'))->assertForbidden();
    }

    public function test_clearing_all_permissions_does_not_restore_role_defaults(): void
    {
        $owner = User::factory()->admin()->create();
        $staff = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $this->actingAs($owner)->put(route('admin.settings.users.update', $staff), [
            ...$this->staffData('admin'),
        ])->assertRedirect();
        $this->assertSame([], $staff->fresh()->permissions);
        $this->actingAs($staff->fresh())->get(route('admin'))->assertOk()->assertDontSee('Business Overview');
        $this->get(route('admin.products.index'))->assertForbidden();
        $this->get(route('admin.settings.general'))->assertForbidden();
        $this->get(route('admin.settings.profile'))->assertOk();
    }

    public function test_invalid_permissions_are_rejected_and_profile_cannot_grant_access(): void
    {
        $owner = User::factory()->admin()->create();
        $this->actingAs($owner)->post(route('admin.settings.users.store'), [
            ...$this->staffData('admin'), 'permissions' => ['roles', 'unknown'],
        ])->assertSessionHasErrors(['permissions.0', 'permissions.1']);
        $staff = User::factory()->create(['is_admin' => true, 'role' => 'manager', 'permissions' => []]);
        $this->actingAs($staff)->put(route('admin.settings.profile.update'), [
            'name' => $staff->name, 'email' => $staff->email, 'permissions' => ['general_settings'],
        ])->assertRedirect();
        $this->assertSame([], $staff->fresh()->permissions);
        $this->get(route('admin.settings.general'))->assertForbidden();
    }

    public function test_super_admin_retains_full_access_even_with_no_selected_permissions(): void
    {
        $owner = User::factory()->admin()->create(['permissions' => []]);
        $this->actingAs($owner);
        foreach (['admin', 'admin.settings.users', 'admin.products.index', 'admin.orders.index', 'admin.tracking', 'admin.settings.general'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_super_admin_can_delete_other_roles_but_no_super_admin_account(): void
    {
        $owner = User::factory()->admin()->create();
        $otherSuperAdmin = User::factory()->admin()->create();
        $this->actingAs($owner);
        foreach (['admin', 'manager', 'customer'] as $role) {
            $target = User::factory()->create(['role' => $role, 'is_admin' => $role !== 'customer']);
            $this->get(route('admin.settings.users'))
                ->assertSee('action="'.route('admin.settings.users.destroy', $target).'"', false)
                ->assertDontSee('action="'.route('admin.settings.users.destroy', $owner).'"', false);
            $this->delete(route('admin.settings.users.destroy', $target))->assertRedirectToRoute('admin.settings.users');
            $this->assertDatabaseMissing('users', ['id' => $target->id]);
        }
        foreach ([$owner, $otherSuperAdmin] as $target) {
            $this->delete(route('admin.settings.users.destroy', $target))->assertForbidden();
            $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'super_admin']);
        }
    }

    public function test_other_roles_cannot_delete_users_by_direct_request(): void
    {
        $target = User::factory()->create();
        foreach (['admin', 'manager', 'customer'] as $role) {
            $actor = User::factory()->create(['role' => $role, 'is_admin' => $role !== 'customer']);
            $this->actingAs($actor)->delete(route('admin.settings.users.destroy', $target))->assertForbidden();
        }
        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    private function staffData(string $role): array
    {
        return [
            'name' => 'Staff '.$role, 'email' => $role.'@example.com', 'phone' => '01800000000',
            'role' => $role, 'password' => 'secure-password', 'password_confirmation' => 'secure-password',
        ];
    }
}
