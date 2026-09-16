<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'discount_type', 'value', 'minimum_order', 'is_active', 'expires_at'])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function isValidFor(float $subtotal): bool
    {
        return $this->is_active
            && $subtotal >= (float) $this->minimum_order
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->discount_type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return min($subtotal, round($discount, 2));
    }
}
