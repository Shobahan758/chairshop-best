<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerAccount extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'email', 'phone', 'referral_code', 'addresses', 'wishlist', 'inbox_read_at'];

    protected function casts(): array
    {
        return ['addresses' => 'array', 'wishlist' => 'array', 'wallet_entries' => 'array', 'loyalty_entries' => 'array', 'inbox_read_at' => 'datetime'];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}
