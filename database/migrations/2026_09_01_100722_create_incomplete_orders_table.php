<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incomplete_orders', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('district')->nullable();
            $table->string('area')->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->json('cart')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomplete_orders');
    }
};
