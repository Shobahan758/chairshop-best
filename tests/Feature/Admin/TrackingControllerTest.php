<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\TrackingEvent;
use App\Models\TrackingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_meta_id_enables_pixel_and_clearing_it_removes_the_script(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        TrackingSetting::create(['tracking_enabled' => false]);

        $this->put(route('admin.tracking.integration.update', 'meta'), ['meta_pixel_id' => '1234567890'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertTrue(TrackingSetting::first()->tracking_enabled);
        $this->get(route('home'))->assertOk()->assertSee("fbq('init', \"1234567890\")", false)->assertSee("fbq('track', 'PageView')", false);
        $this->put(route('admin.tracking.integration.update', 'meta'), ['meta_pixel_id' => ''])->assertRedirect();
        $this->get(route('home'))->assertOk()->assertDontSee('fbevents.js', false);
    }

    public function test_meta_pixel_id_must_be_numeric(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.tracking.integration.update', 'meta'), ['meta_pixel_id' => 'not-a-pixel'])
            ->assertSessionHasErrors('meta_pixel_id');
        $this->assertDatabaseCount('tracking_settings', 0);
    }

    public function test_meta_tracks_product_cart_checkout_and_successful_purchase_once(): void
    {
        TrackingSetting::create(['tracking_enabled' => true, 'meta_pixel_id' => '1234567890']);
        $product = Product::factory()->create(['price' => 2000, 'sale_price' => null, 'stock' => 5]);

        $this->get(route('product', $product))->assertOk()->assertSee("fbq('track', 'ViewContent'", false);
        $this->from(route('product', $product))->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect();
        $this->get(route('product', $product))->assertOk()->assertSee('AddToCart')->assertSee('4000', false);
        $this->get(route('checkout'))->assertOk()->assertSee("fbq('track', 'InitiateCheckout'", false)->assertDontSee('AddToCart');
        $this->post(route('checkout.store'), [
            'name' => 'Pixel Customer', 'phone' => '01700000000', 'district' => 'Dhaka',
            'area' => 'Dhanmondi', 'address' => 'A complete delivery address', 'payment_method' => 'cod',
        ])->assertSessionHasNoErrors()->assertRedirectToRoute('dashboard');

        $this->get(route('dashboard'))->assertOk()->assertSee('Purchase')->assertSee('4080', false)->assertSee('BDT');
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Purchase');
    }

    public function test_admin_can_open_site_tracking(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.tracking'))->assertSee('Site Tracking')->assertSee('Meta Pixel');
    }

    public function test_site_tracking_excludes_account_and_admin_page_events(): void
    {
        $admin = User::factory()->admin()->create();
        foreach (['/', '/login', '/dashboard', '/admin'] as $path) {
            TrackingEvent::query()->create(['event' => 'page_view', 'path' => $path, 'session_hash' => hash('sha256', $path)]);
        }

        $this->actingAs($admin)->get(route('admin.tracking'))->assertOk()->assertSee('1 total events');
    }

    public function test_customer_is_forbidden_from_site_tracking(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.tracking'))->assertForbidden();
    }

    public function test_admin_can_save_pixel_ids(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.tracking.update'), [
            'tracking_enabled' => true,
            'meta_pixel_id' => '1234567890',
            'google_tag_id' => 'G-ABC123',
        ])->assertRedirect();

        $this->assertSame('1234567890', TrackingSetting::first()->meta_pixel_id);
    }

    public function test_admin_can_open_each_tracking_integration(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['meta' => 'Meta Pixel ID', 'google' => 'Google Tag ID', 'tiktok' => 'TikTok Pixel ID', 'other' => 'Pinterest Tag ID'] as $integration => $label) {
            $this->actingAs($admin)->get(route('admin.tracking.integration', $integration))->assertOk()->assertSee($label);
        }
    }

    public function test_saving_one_integration_preserves_other_tracking_settings(): void
    {
        $admin = User::factory()->admin()->create();
        TrackingSetting::query()->create(['google_tag_id' => 'G-EXISTING']);

        $this->actingAs($admin)->put(route('admin.tracking.integration.update', 'meta'), [
            'meta_pixel_id' => '1234567890',
        ])->assertRedirect();

        $settings = TrackingSetting::first();

        $this->assertSame('1234567890', $settings->meta_pixel_id);
        $this->assertSame('G-EXISTING', $settings->google_tag_id);
    }
}
