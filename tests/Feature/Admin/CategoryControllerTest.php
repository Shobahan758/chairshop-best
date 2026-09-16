<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_category_page_to_login(): void
    {
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
    }

    public function test_customer_is_forbidden_from_category_page(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.categories.index'))->assertForbidden();
    }

    public function test_admin_can_open_category_page(): void
    {
        $admin = User::factory()->admin()->create();
        Category::create(['name' => 'Office Chairs', 'slug' => 'office-chairs', 'icon' => 'briefcase']);

        $this->actingAs($admin)->get(route('admin.categories.index'))->assertSee('Office Chairs');
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Reading Chairs', 'icon' => 'book',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Reading Chairs', 'slug' => 'reading-chairs', 'icon' => 'book',
        ]);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        Category::create(['name' => 'Office', 'slug' => 'office']);

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Another Office', 'slug' => 'office',
        ])->assertSessionHasErrors(['slug' => 'This category slug is already in use.']);

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Office', 'slug' => 'office']);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), ['name' => 'Office Chairs', 'slug' => 'office', 'icon' => 'briefcase'])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Office Chairs', 'slug' => 'office']);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.categories.toggle', $category))->assertRedirect();

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_admin_can_delete_an_empty_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertRedirect();

        $this->assertModelMissing($category);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))->assertSessionHas('error');

        $this->assertModelExists($category);
    }
}
