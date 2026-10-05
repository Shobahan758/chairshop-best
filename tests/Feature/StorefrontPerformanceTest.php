<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontPerformanceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_homepage_reads_site_settings_once_for_page_and_layout(): void
    {
        GeneralSetting::create(['site_content' => ['shared' => ['footer' => ['field_3' => 'Our comfortable chairs']]]]);
        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->get(route('home'));
        $settingsQueries = collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains($query['query'], 'from `general_settings`'));
        DB::disableQueryLog();

        $response->assertSee('Our comfortable chairs');
        $this->assertCount(1, $settingsQueries);
    }

    public function test_storefront_uses_local_bootstrap_assets(): void
    {
        $this->get(route('home'))
            ->assertSee('bootstrap-5.3.8.min.css')
            ->assertSee('bootstrap-icons-1.13.1.min.css')
            ->assertSee('bootstrap-5.3.8.bundle.min.js')
            ->assertDontSee('cdn.jsdelivr.net');
    }
}
