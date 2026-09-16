<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['store_name', 'support_email', 'support_phone', 'bkash_number', 'nagad_number', 'currency_code', 'currency_symbol', 'timezone', 'business_address', 'maintenance_message', 'site_content'])]
class GeneralSetting extends Model
{
    protected function casts(): array
    {
        return ['site_content' => 'array'];
    }

    protected $attributes = [
        'store_name' => 'ChairGhor',
        'currency_code' => 'BDT',
        'currency_symbol' => '৳',
        'timezone' => 'Asia/Dhaka',
    ];
}
