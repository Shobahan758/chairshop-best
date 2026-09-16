<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_suggests_the_next_sku_automatically(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Office']);

        $this->actingAs($admin)
            ->get(route('admin.products.create'))
            ->assertSee('data-sku-number="1"', false)
            ->assertSee('data-sku-initial="O"', false)
            ->assertSee('readonly', false);
    }

    public function test_product_image_upload_controls_render_without_broken_blade_directives(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['additional_images' => ['https://example.com/side.jpg']]);

        $this->actingAs($admin)->get(route('admin.products.create'))
            ->assertSee('Add image')
            ->assertSee('data-additional-image-button', false)
            ->assertSee('alt="Additional image 1 preview"  hidden', false)
            ->assertDontSee('@hidden', false);

        $this->get(route('admin.products.edit', $product))
            ->assertSee('Change image')
            ->assertSee('src="https://example.com/side.jpg" alt="Additional image 1 preview" >', false)
            ->assertDontSee('@hidden', false);
    }

    public function test_missing_sku_is_generated_when_product_is_created(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Office']);
        Storage::fake('public');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Automatic SKU Chair', 'price' => 20000, 'stock' => 8, 'image' => UploadedFile::fake()->image('chair.jpg', 1200, 1200),
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Automatic SKU Chair', 'sku' => 'O-1', 'material' => null, 'color' => null, 'size' => null]);
    }

    public function test_optional_product_tags_and_size_are_saved_and_can_be_cleared(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Storage::fake('public');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Sized Chair', 'price' => 1000, 'stock' => 1,
            'material' => 'Wood, Fabric', 'color' => 'Black, White', 'size' => '60 × 60 × 90 cm',
            'image' => UploadedFile::fake()->image('chair.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Sized Chair')->firstOrFail();
        $this->assertDatabaseHas('products', [
            'id' => $product->id, 'material' => 'Wood, Fabric', 'color' => 'Black, White', 'size' => '60 × 60 × 90 cm',
        ]);
        $this->get(route('admin.products.edit', $product))
            ->assertSee('Wood, Fabric')->assertSee('Black, White')->assertSee('60 × 60 × 90 cm');

        $this->put(route('admin.products.update', $product), [
            'category_id' => $category->id, 'name' => $product->name, 'slug' => $product->slug,
            'price' => 1000, 'stock' => 1, 'material' => '', 'color' => '', 'size' => '',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'material' => null, 'color' => null, 'size' => null]);
    }

    public function test_sku_serials_are_not_reused_after_deletion_or_category_changes(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Office']);
        $subcategory = Subcategory::factory()->for($category)->create(['name' => 'Mesh']);
        $otherCategory = Category::factory()->create(['name' => 'Gaming']);
        Storage::fake('public');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'subcategory_id' => $subcategory->id,
            'name' => 'First Chair', 'sku' => 'FORGED-1001', 'price' => 1000, 'stock' => 1,
            'image' => UploadedFile::fake()->image('first.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'First Chair')->firstOrFail();
        $this->assertSame('OM-1', $product->sku);
        $this->delete(route('admin.products.destroy', $product))->assertRedirect();
        $this->assertModelMissing($product);

        $this->post(route('admin.products.store'), [
            'category_id' => $otherCategory->id, 'name' => 'Second Chair',
            'sku' => 'OM-1', 'price' => 1000, 'stock' => 1,
            'image' => UploadedFile::fake()->image('second.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Second Chair', 'sku' => 'G-2']);
    }

    public function test_bangla_category_initials_are_used_in_generated_skus(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'অফিস']);
        $subcategory = Subcategory::factory()->for($category)->create(['name' => 'চেয়ার']);
        Storage::fake('public');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'subcategory_id' => $subcategory->id,
            'name' => 'Bangla Chair', 'price' => 1000, 'stock' => 1,
            'image' => UploadedFile::fake()->image('chair.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Bangla Chair', 'sku' => 'অচ-1']);
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Office']);
        $subcategory = Subcategory::factory()->for($category)->create(['name' => 'Mesh']);
        $brand = Brand::factory()->create();
        Storage::fake('public');
        $additionalImages = [
            UploadedFile::fake()->image('chair-side.jpg', 1400, 1400),
            UploadedFile::fake()->image('chair-back.jpg', 1400, 1400),
            UploadedFile::fake()->image('chair-detail.jpg', 1400, 1400),
            UploadedFile::fake()->image('chair-room.jpg', 1400, 1400),
        ];

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'subcategory_id' => $subcategory->id, 'brand_id' => $brand->id, 'name' => 'Ergonomic Chair', 'sku' => 'CG-900', 'price' => 20000, 'discount_percentage' => 10, 'stock' => 8, 'image' => UploadedFile::fake()->image('chair.jpg', 1600, 1600), 'additional_images' => $additionalImages, 'featured' => '1', 'just_for_you' => '1', 'office_essential' => '1', 'gaming_pick' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['category_id' => $category->id, 'subcategory_id' => $subcategory->id, 'brand_id' => $brand->id, 'slug' => 'ergonomic-chair', 'sku' => 'OM-1', 'short_description' => 'Ergonomic Chair', 'sale_price' => 18000, 'stock' => 8, 'featured' => true, 'just_for_you' => true, 'office_essential' => true, 'gaming_pick' => true]);

        $product = Product::where('sku', 'OM-1')->firstOrFail();
        $this->assertCount(4, $product->additional_images);

        foreach ([$product->image, ...$product->additional_images] as $imageUrl) {
            $path = str($imageUrl)->after('/storage/')->toString();
            Storage::disk('public')->assertExists($path);
            $this->assertLessThanOrEqual(40 * 1024, Storage::disk('public')->size($path));
        }

        $this->get(route('product', $product))
            ->assertSee($product->additional_images[0])
            ->assertSee($product->additional_images[3]);
    }

    public function test_subcategory_must_belong_to_the_selected_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'subcategory_id' => $subcategory->id, 'name' => 'Ergonomic Chair', 'sku' => 'CG-900', 'price' => 20000, 'stock' => 8, 'image' => 'https://example.com/chair.jpg',
        ])->assertSessionHasErrors('subcategory_id');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_sale_price_must_be_less_than_regular_price(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Ergonomic Chair', 'slug' => 'ergonomic-chair', 'sku' => 'CG-900', 'short_description' => 'Comfortable office chair.', 'price' => 20000, 'sale_price' => 22000, 'stock' => 8, 'image' => 'https://example.com/chair.jpg',
        ])->assertSessionHasErrors('sale_price');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_discount_percentage_cannot_exceed_one_hundred(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Ergonomic Chair', 'sku' => 'CG-900', 'price' => 20000, 'discount_percentage' => 101, 'stock' => 8, 'image' => 'https://example.com/chair.jpg',
        ])->assertSessionHasErrors('discount_percentage');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_can_have_more_than_four_additional_images(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Storage::fake('public');

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Gallery Chair', 'price' => 20000, 'stock' => 8,
            'image' => UploadedFile::fake()->image('main.jpg'),
            'additional_images' => [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
                UploadedFile::fake()->image('three.jpg'),
                UploadedFile::fake()->image('four.jpg'),
                UploadedFile::fake()->image('five.jpg'),
            ],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Gallery Chair')->firstOrFail();
        $this->assertCount(5, $product->additional_images);
        foreach ($product->additional_images as $image) {
            Storage::disk('public')->assertExists(str($image)->after('/storage/')->toString());
        }
        $this->get(route('admin.products.edit', $product))
            ->assertSee('additionalImage4')
            ->assertSee($product->additional_images[4]);
    }

    public function test_adding_an_image_keeps_existing_product_images(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['additional_images' => ['https://example.com/side.jpg']]);
        Storage::fake('public');

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $product->category_id, 'name' => $product->name,
            'slug' => $product->slug, 'price' => 1000, 'stock' => 1,
            'additional_images' => [4 => UploadedFile::fake()->image('new.jpg')],
        ])->assertRedirect(route('admin.products.index'));

        $images = $product->fresh()->additional_images;
        $this->assertCount(2, $images);
        $this->assertSame('https://example.com/side.jpg', $images[0]);
        Storage::disk('public')->assertExists(str($images[1])->after('/storage/')->toString());
    }

    public function test_low_stock_page_only_shows_products_below_ten(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->create(['name' => 'Low Chair', 'stock' => 9]);
        Product::factory()->create(['name' => 'Healthy Chair', 'stock' => 10]);

        $this->actingAs($admin)->get(route('admin.products.low-stock'))->assertSee('Low Chair')->assertDontSee('Healthy Chair');
    }

    public function test_sale_page_only_shows_discounted_products(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->create(['name' => 'Sale Chair', 'price' => 20000, 'sale_price' => 18000]);
        Product::factory()->create(['name' => 'Regular Chair', 'sale_price' => null]);

        $this->actingAs($admin)->get(route('admin.products.sale'))->assertSee('Sale Chair')->assertDontSee('Regular Chair');
    }

    public function test_admin_can_update_a_product_without_replacing_its_images(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['name' => 'Old Chair', 'slug' => 'old-chair', 'sku' => 'CG-5000', 'image' => 'https://example.com/main.jpg', 'additional_images' => ['https://example.com/side.jpg']]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'name' => 'Updated Chair',
            'slug' => 'updated-chair',
            'sku' => 'FORGED-9999',
            'short_description' => 'Updated description',
            'price' => 25000,
            'discount_percentage' => 10,
            'stock' => 12,
        ])->assertRedirectToRoute('admin.products.index');

        $product->refresh();
        $this->assertSame('Updated Chair', $product->name);
        $this->assertSame('CG-5000', $product->sku);
        $this->assertSame('22500.00', $product->sale_price);
        $this->assertSame('https://example.com/main.jpg', $product->image);
        $this->assertSame(['https://example.com/side.jpg'], $product->additional_images);
    }

    public function test_admin_can_deactivate_and_reactivate_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch(route('admin.products.toggle', $product))->assertRedirect();
        $this->assertFalse($product->fresh()->is_active);

        $this->actingAs($admin)->patch(route('admin.products.toggle', $product))->assertRedirect();
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_inactive_product_is_hidden_from_storefront(): void
    {
        $product = Product::factory()->create(['name' => 'Hidden Chair', 'featured' => true, 'is_active' => false]);

        $this->get(route('home'))->assertOk()->assertDontSee('Hidden Chair');
        $this->get(route('shop'))->assertOk()->assertDontSee('Hidden Chair');
        $this->get(route('product', $product))->assertNotFound();
    }

    public function test_admin_can_delete_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['image' => 'https://example.com/main.jpg']);

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))->assertRedirect();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
