<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
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

    public function test_admin_can_edit_without_changing_the_slug(): void
    {
        $record = Subcategory::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.subcategories.update', $record), ['name' => 'Updated', 'slug' => $record->slug, 'category_id' => $record->category_id])
            ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.subcategories.index');
        $this->assertSame('Updated', $record->fresh()->name);
    }

    public function test_edit_rejects_another_records_slug(): void
    {
        $record = Subcategory::factory()->create();
        $other = Subcategory::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.subcategories.update', $record), ['name' => 'Updated', 'slug' => $other->slug, 'category_id' => $record->category_id])
            ->assertSessionHasErrors('slug');
        $this->assertSame($record->slug, $record->fresh()->slug);
    }

    public function test_admin_can_delete_an_unused_record(): void
    {
        $record = Subcategory::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.subcategories.destroy', $record))->assertRedirectToRoute('admin.subcategories.index');
        $this->assertModelMissing($record);
    }

    public function test_record_with_products_cannot_be_deleted(): void
    {
        $record = Subcategory::factory()->create();
        $product = Product::factory()->create(['subcategory_id' => $record->id, 'category_id' => $record->category_id]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.subcategories.destroy', $record))->assertSessionHas('error');
        $this->assertModelExists($record);
        $this->assertModelExists($product);
    }

    public function test_customer_cannot_edit_or_delete_records(): void
    {
        $record = Subcategory::factory()->create();
        $customer = User::factory()->create();

        $this->actingAs($customer)->put(route('admin.subcategories.update', $record), ['name' => 'Updated', 'slug' => $record->slug, 'category_id' => $record->category_id])->assertForbidden();
        $this->delete(route('admin.subcategories.destroy', $record))->assertForbidden();
        $this->assertModelExists($record);
        $this->assertSame($record->name, $record->fresh()->name);
    }

    public function test_list_shows_edit_and_delete_controls(): void
    {
        $record = Subcategory::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.subcategories.index'))
            ->assertSee('editSubcategoryModal'.$record->id)
            ->assertSee(route('admin.subcategories.update', $record))
            ->assertSee(route('admin.subcategories.destroy', $record));
    }

    public function test_parent_category_cannot_change_while_products_are_attached(): void
    {
        $record = Subcategory::factory()->create();
        Product::factory()->create(['subcategory_id' => $record->id, 'category_id' => $record->category_id]);
        $otherCategory = Category::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.subcategories.update', $record), [
            'name' => $record->name, 'slug' => $record->slug, 'category_id' => $otherCategory->id,
        ])->assertSessionHasErrors('category_id');
        $this->assertSame($record->category_id, $record->fresh()->category_id);
    }

    public function test_unused_subcategory_can_move_to_another_category(): void
    {
        $record = Subcategory::factory()->create();
        $otherCategory = Category::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.subcategories.update', $record), [
            'name' => $record->name, 'slug' => $record->slug, 'category_id' => $otherCategory->id,
        ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.subcategories.index');
        $this->assertSame($otherCategory->id, $record->fresh()->category_id);
    }

    public function test_staff_without_catalog_permission_cannot_modify_records(): void
    {
        $record = Subcategory::factory()->create();
        $staff = User::factory()->create(['is_admin' => true, 'role' => 'manager', 'permissions' => ['products']]);

        $this->actingAs($staff)->put(route('admin.subcategories.update', $record), ['name' => 'Denied'])->assertForbidden();
        $this->delete(route('admin.subcategories.destroy', $record))->assertForbidden();
        $this->assertSame($record->name, $record->fresh()->name);
        $this->assertModelExists($record);
    }

    public function test_failed_edit_preserves_input_and_reopens_the_edit_modal(): void
    {
        $record = Subcategory::factory()->create();
        $other = Subcategory::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->from(route('admin.subcategories.index'))
            ->put(route('admin.subcategories.update', $record), ['name' => 'Retried name', 'slug' => $other->slug, '_editing_id' => $record->id, 'category_id' => $record->category_id])->assertRedirectToRoute('admin.subcategories.index');

        $this->get(route('admin.subcategories.index'))->assertSee('Retried name')->assertSee('This sub category slug is already in use.')->assertSee('new bootstrap.Modal(modal).show()', false);
    }
}
