<?php

namespace Tests\Feature\Admin;

use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\User;
use App\Services\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_product_delivery_return_and_warranty_text(): void
    {
        $product = Product::factory()->create();
        $this->get(route('product', $product))->assertOk()->assertSee('২–৫ কর্মদিবস')->assertSee('১ বছর পর্যন্ত');
        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('admin.settings.site', ['page' => 'product', 'section' => 'assurances']))
            ->assertOk()->assertSee('ডেলিভারির শিরোনাম')->assertSee('ওয়ারেন্টির বিবরণ');
        $content = [
            'delivery_title' => 'Express delivery', 'delivery_text' => 'Next business day',
            'returns_title' => 'Flexible returns', 'returns_text' => 'Within 14 days',
            'warranty_title' => 'Extended warranty', 'warranty_text' => '<b>Two years</b>',
        ];

        $this->put(route('admin.settings.site.update', ['product', 'assurances']), ['content' => $content])
            ->assertSessionHasNoErrors()->assertRedirect();

        $savedContent = GeneralSetting::first()->site_content['product']['assurances'];
        foreach ($content as $field => $text) {
            $this->assertSame($text, $savedContent[$field]);
        }
        $response = $this->get(route('product', $product))->assertOk();
        foreach ($content as $text) {
            $response->assertSee($text);
        }
        $response->assertDontSee('<b>Two years</b>', false)->assertDontSee('২–৫ কর্মদিবস');
    }

    public function test_admin_can_upload_and_remove_header_and_footer_logos(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        foreach (['header', 'footer'] as $section) {
            $content = array_map(fn (array $field): string => $field['default'], config('site_content.shared.'.$section));
            $this->put(route('admin.settings.site.update', ['shared', $section]), [
                'content' => $content,
                'uploads' => ['logo' => UploadedFile::fake()->image($section.'.png', 400, 100)],
            ])->assertSessionHasNoErrors()->assertRedirect();

            $logo = GeneralSetting::first()->site_content['shared'][$section]['logo'];
            Storage::disk('public')->assertExists('site-content/'.basename($logo));
            $this->get(route('home'))->assertSee($logo, false);

            $this->put(route('admin.settings.site.update', ['shared', $section]), ['content' => $content])
                ->assertSessionHasNoErrors()->assertRedirect();
            $this->get(route('home'))->assertDontSee($logo, false)->assertSee($content['field_1']);
        }
    }

    public function test_site_menu_links_open_the_selected_content_editor(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $response = $this->get(route('admin.settings.site'));
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);

        foreach (['Hero Banner' => 'banner_1', 'Chair Guide' => 'guide', 'Customer Reviews' => 'review_1', 'Service Benefits' => 'services', 'Header' => 'header', 'Footer' => 'footer'] as $label => $section) {
            $link = $xpath->query('//div[@id="siteMenu"]/a[normalize-space(.)="'.$label.'"]')->item(0);
            $this->assertNotNull($link);
            $destination = $this->get($link->getAttribute('href'))->assertOk();
            $editor = new \DOMDocument;
            @$editor->loadHTML($destination->getContent());
            $editorXpath = new \DOMXPath($editor);
            $this->assertSame(1, $editorXpath->query('//details[@id="section-'.$section.'"][@open]')->length);
        }

        $response->assertDontSee('href="#site-sections"', false);
    }

    public function test_admin_can_edit_each_page(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        foreach (SiteContent::pages() as $page => $label) {
            $this->get(route('admin.settings.site', ['page' => $page]))->assertOk()->assertSee($label);
        }
    }

    public function test_saved_section_is_rendered_without_overwriting_other_settings(): void
    {
        GeneralSetting::create(['store_name' => 'Existing Store', 'site_content' => ['shared' => ['footer' => ['field_3' => 'Existing footer']]]]);
        $this->actingAs(User::factory()->admin()->create());
        $this->put(route('admin.settings.site.update', ['about', 'content']), ['content' => [
            'title' => 'Our updated story', 'body' => '<script>alert(1)</script>', 'help' => '',
        ]])->assertSessionHasNoErrors()->assertRedirect();

        $settings = GeneralSetting::first();
        $this->assertSame('Existing Store', $settings->store_name);
        $this->assertSame('Existing footer', $settings->site_content['shared']['footer']['field_3']);
        $this->get(route('page', 'about'))->assertSee('Our updated story')->assertSee('<script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('page', 'delivery'))->assertSee('ডেলিভারি নীতিমালা');
    }

    public function test_homepage_uses_defaults_and_admin_can_replace_banner_with_uploaded_image(): void
    {
        Storage::fake('public');
        $this->get(route('home'))->assertOk()->assertSee('আপনার বসার গল্পে');
        $content = array_map(fn (array $field): string => $field['default'], config('site_content.home.banner_1'));
        $content['field_2'] = 'নতুন ব্যানার';
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.settings.site.update', ['home', 'banner_1']), [
            'content' => $content,
            'uploads' => ['field_7' => UploadedFile::fake()->image('banner.jpg')],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $image = GeneralSetting::first()->site_content['home']['banner_1']['field_7'];
        Storage::disk('public')->assertExists('site-content/'.basename($image));
        $this->get(route('home'))->assertSee('নতুন ব্যানার')->assertSee($image, false);
    }

    public function test_unsafe_links_and_unknown_content_are_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $content = array_map(fn (array $field): string => $field['default'], config('site_content.home.banner_1'));
        $content['field_6'] = 'javascript:alert(1)';
        $content['unknown'] = 'unexpected';
        $this->put(route('admin.settings.site.update', ['home', 'banner_1']), ['content' => $content])
            ->assertSessionHasErrors(['content', 'content.field_6']);
        $this->assertDatabaseCount('general_settings', 0);
        $this->put(route('admin.settings.site.update', ['home', 'unknown']), ['content' => ['title' => 'test']])->assertNotFound();
    }

    public function test_guests_and_customers_cannot_read_or_change_site_content(): void
    {
        $this->get(route('admin.settings.site'))->assertRedirect(route('login'));
        $this->put(route('admin.settings.site.update', ['about', 'content']))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.settings.site'))->assertForbidden();
        $this->put(route('admin.settings.site.update', ['about', 'content']))->assertForbidden();
        $this->assertDatabaseCount('general_settings', 0);
    }

    public function test_store_pages_render_default_content(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        foreach (['contact', 'shop', 'track', 'cart', 'login'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->actingAs(User::factory()->create());
        foreach (['profile', 'orders', 'wishlist', 'wallet', 'loyalty', 'inbox', 'addresses', 'support', 'referrals', 'coupons', 'track'] as $section) {
            $this->get(route('dashboard', ['section' => $section]))->assertOk();
        }
    }
}
