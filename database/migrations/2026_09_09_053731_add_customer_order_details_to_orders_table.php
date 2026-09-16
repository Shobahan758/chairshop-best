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
        Schema::table('orders', function (Blueprint $table) {
            $table->json('status_history')->nullable();
            $table->string('delivery_name')->nullable();
            $table->string('delivery_phone', 30)->nullable();
            $table->string('courier_tracking_url', 2048)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['status_history', 'delivery_name', 'delivery_phone', 'courier_tracking_url']);
        });
    }
};
