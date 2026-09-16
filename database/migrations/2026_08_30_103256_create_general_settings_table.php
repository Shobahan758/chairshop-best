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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('ChairGhor');
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('currency_code', 3)->default('BDT');
            $table->string('currency_symbol', 10)->default('৳');
            $table->string('timezone', 50)->default('Asia/Dhaka');
            $table->text('business_address')->nullable();
            $table->text('maintenance_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
