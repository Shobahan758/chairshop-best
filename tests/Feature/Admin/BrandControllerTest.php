<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_brand(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.brands.store'), ['name' => 'ChairGhor', 'logo' => 'https://example.com/logo.png'])->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('brands', ['name' => 'ChairGhor', 'slug' => 'chairghor', 'logo' => 'https://example.com/logo.png']);
    }

    public function test_duplicate_brand_slug_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        Brand::factory()->create(['slug' => 'chairghor']);

        $this->actingAs($admin)->post(route('admin.brands.store'), ['name' => 'Another Brand', 'slug' => 'chairghor'])->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('brands', 1);
    }
}
