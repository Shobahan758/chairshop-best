<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sku_sequences', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedBigInteger('last_number');
        });

        $lastNumber = 1000 + (int) DB::table('products')->max('id');

        foreach (DB::table('products')->select('id', 'sku')->lazyById() as $product) {
            if (preg_match('/-(\d+)$/', $product->sku, $matches)) {
                $lastNumber = max($lastNumber, (int) $matches[1]);
            }
        }

        DB::table('product_sku_sequences')->insert(['id' => 1, 'last_number' => $lastNumber]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sku_sequences');
    }
};
