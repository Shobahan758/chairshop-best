<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Product;
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

    public function test_admin_can_edit_without_changing_the_slug(): void
    {
        $record = Brand::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.brands.update', $record), ['name' => 'Updated', 'slug' => $record->slug, 'logo' => null])
            ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.brands.index');
        $this->assertSame('Updated', $record->fresh()->name);
    }

    public function test_edit_rejects_another_records_slug(): void
    {
        $record = Brand::factory()->create();
        $other = Brand::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.brands.update', $record), ['name' => 'Updated', 'slug' => $other->slug])
            ->assertSessionHasErrors('slug');
        $this->assertSame($record->slug, $record->fresh()->slug);
    }

    public function test_admin_can_delete_an_unused_record(): void
    {
        $record = Brand::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.brands.destroy', $record))->assertRedirectToRoute('admin.brands.index');
        $this->assertModelMissing($record);
    }

    public function test_record_with_products_cannot_be_deleted(): void
    {
        $record = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $record->id]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.brands.destroy', $record))->assertSessionHas('error');
        $this->assertModelExists($record);
        $this->assertModelExists($product);
    }

    public function test_customer_cannot_edit_or_delete_records(): void
    {
        $record = Brand::factory()->create();
        $customer = User::factory()->create();

        $this->actingAs($customer)->put(route('admin.brands.update', $record), ['name' => 'Updated', 'slug' => $record->slug, 'logo' => null])->assertForbidden();
        $this->delete(route('admin.brands.destroy', $record))->assertForbidden();
        $this->assertModelExists($record);
        $this->assertSame($record->name, $record->fresh()->name);
    }

    public function test_list_shows_edit_and_delete_controls(): void
    {
        $record = Brand::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.brands.index'))
            ->assertSee('editBrandModal'.$record->id)
            ->assertSee(route('admin.brands.update', $record))
            ->assertSee(route('admin.brands.destroy', $record));
    }

    public function test_staff_without_catalog_permission_cannot_modify_records(): void
    {
        $record = Brand::factory()->create();
        $staff = User::factory()->create(['is_admin' => true, 'role' => 'manager', 'permissions' => ['products']]);

        $this->actingAs($staff)->put(route('admin.brands.update', $record), ['name' => 'Denied'])->assertForbidden();
        $this->delete(route('admin.brands.destroy', $record))->assertForbidden();
        $this->assertSame($record->name, $record->fresh()->name);
        $this->assertModelExists($record);
    }

    public function test_failed_edit_preserves_input_and_reopens_the_edit_modal(): void
    {
        $record = Brand::factory()->create();
        $other = Brand::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->from(route('admin.brands.index'))
            ->put(route('admin.brands.update', $record), ['name' => 'Retried name', 'slug' => $other->slug, '_editing_id' => $record->id])->assertRedirectToRoute('admin.brands.index');

        $this->get(route('admin.brands.index'))->assertSee('Retried name')->assertSee('This brand slug is already in use.')->assertSee('new bootstrap.Modal(modal).show()', false);
    }
}
