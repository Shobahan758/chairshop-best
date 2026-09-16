<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

class HomeProductSectionsTest extends TestCase
{
    public function test_products_only_appear_in_selected_homepage_sections(): void
    {
        $category = Category::factory()->create();
        $popularProduct = Product::factory()->for($category)->create(['featured' => true]);
        $justForYouProduct = Product::factory()->for($category)->create(['just_for_you' => true]);
        $officeProduct = Product::factory()->for($category)->create(['office_essential' => true]);
        $gamingProduct = Product::factory()->for($category)->create(['gaming_pick' => true]);
        $unselectedProduct = Product::factory()->for($category)->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('featured', fn ($products) => $products->contains($popularProduct) && ! $products->contains($unselectedProduct))
            ->assertViewHas('justForYou', fn ($products) => $products->contains($justForYouProduct) && ! $products->contains($unselectedProduct))
            ->assertViewHas('officeProducts', fn ($products) => $products->contains($officeProduct) && ! $products->contains($unselectedProduct))
            ->assertViewHas('gamingProducts', fn ($products) => $products->contains($gamingProduct) && ! $products->contains($unselectedProduct));
    }
}
