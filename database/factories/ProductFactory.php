<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'subcategory_id' => null,
            'brand_id' => null,
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'sku' => fake()->unique()->bothify('CG-###??'),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(5000, 50000),
            'sale_price' => null,
            'stock' => fake()->numberBetween(0, 50),
            'image' => fake()->imageUrl(900, 900),
            'additional_images' => null,
            'material' => fake()->word(),
            'color' => fake()->safeColorName(),
            'featured' => false,
            'just_for_you' => false,
            'office_essential' => false,
            'gaming_pick' => false,
            'is_active' => true,
        ];
    }
}
