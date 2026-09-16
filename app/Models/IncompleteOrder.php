<?php

namespace App\Models;

use Database\Factories\IncompleteOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncompleteOrder extends Model
{
    /** @use HasFactory<IncompleteOrderFactory> */
    use HasFactory;

    protected $fillable = ['session_id', 'name', 'phone', 'email', 'district', 'area', 'address', 'quantity', 'cart'];

    protected $casts = ['cart' => 'array', 'quantity' => 'integer'];
}
