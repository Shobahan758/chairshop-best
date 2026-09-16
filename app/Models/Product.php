<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'subcategory_id', 'brand_id', 'name', 'slug', 'sku', 'short_description', 'description', 'price', 'sale_price', 'stock', 'image', 'additional_images', 'material', 'color', 'size', 'featured', 'just_for_you', 'office_essential', 'gaming_pick', 'is_active'];

    protected $casts = [
        'featured' => 'boolean',
        'just_for_you' => 'boolean',
        'office_essential' => 'boolean',
        'gaming_pick' => 'boolean',
        'is_active' => 'boolean',
        'additional_images' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function getCurrentPriceAttribute()
    {
        return $this->sale_price ?: $this->price;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
