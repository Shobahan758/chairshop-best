<?php

namespace Tests\Feature\Admin;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_general_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.settings.general.update'), [
            'store_name' => 'Chair House',
            'support_email' => 'support@example.com',
            'support_phone' => '01700000000',
            'bkash_number' => '01800000000',
            'nagad_number' => '01900000000',
            'currency_code' => 'BDT',
            'currency_symbol' => '৳',
            'timezone' => 'Asia/Dhaka',
            'business_address' => 'Dhaka',
            'maintenance_message' => 'We will be back soon.',
        ])->assertRedirect();

        $settings = GeneralSetting::first();

        $this->assertSame('Chair House', $settings->store_name);
        $this->assertSame('01800000000', $settings->bkash_number);
    }

    public function test_users_page_can_search_by_name_or_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Matching Customer', 'email' => 'match@example.com']);
        User::factory()->create(['name' => 'Hidden Customer', 'email' => 'hidden@example.com']);

        $this->actingAs($admin)->get(route('admin.settings.users', ['search' => 'Matching']))
            ->assertOk()
            ->assertSee('Matching Customer')
            ->assertDontSee('Hidden Customer');
    }

    public function test_admin_can_update_own_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.settings.profile.update'), [
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
            'phone' => '01800000000',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Updated Admin', 'phone' => '01800000000']);
    }

    public function test_admin_can_change_password(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'old-password']);

        $this->actingAs($admin)->put(route('admin.settings.password.update'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $admin->fresh()->password));
    }

    public function test_admin_can_upload_a_profile_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg', 300, 300)->size(500);

        $this->actingAs($admin)->put(route('admin.settings.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => '',
            'avatar' => $avatar,
        ])->assertRedirect();

        $avatarPath = $admin->fresh()->avatar_path;

        $this->assertNotNull($avatarPath);
        Storage::disk('public')->assertExists($avatarPath);
    }

    public function test_customer_cannot_access_admin_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.settings.general'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.settings.users'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.settings.profile'))->assertForbidden();
    }
}
