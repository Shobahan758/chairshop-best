<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('product_sku_sequences')->insert(['id' => 2, 'last_number' => 0]);
    }

    public function down(): void
    {
        DB::table('product_sku_sequences')->where('id', 2)->delete();
    }
};
