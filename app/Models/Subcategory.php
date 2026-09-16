<?php

namespace App\Models;

use Database\Factories\SubcategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcategory extends Model
{
    /** @use HasFactory<SubcategoryFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'slug'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
