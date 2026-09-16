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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('district');
            $table->string('area');
            $table->text('address');
            $table->string('payment_method')->default('cod');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_charge', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('অর্ডার গ্রহণ');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
