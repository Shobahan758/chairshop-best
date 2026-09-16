<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_subcategory(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.subcategories.store'), ['category_id' => $category->id, 'name' => 'Executive Chairs'])->assertRedirect(route('admin.subcategories.index'));

        $this->assertDatabaseHas('subcategories', ['category_id' => $category->id, 'name' => 'Executive Chairs', 'slug' => 'executive-chairs']);
    }

    public function test_invalid_parent_category_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.subcategories.store'), ['category_id' => 999999, 'name' => 'Executive Chairs', 'slug' => 'executive-chairs'])->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('subcategories', 0);
    }
}
