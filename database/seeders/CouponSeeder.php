<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::updateOrCreate(['code' => 'CHAIR10'], [
            'discount_type' => 'percent',
            'value' => 10,
            'minimum_order' => 1000,
            'is_active' => true,
        ]);
    }
}
