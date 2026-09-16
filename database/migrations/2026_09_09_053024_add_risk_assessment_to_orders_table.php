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
            $table->ipAddress('source_ip')->nullable();
            $table->unsignedSmallInteger('risk_score')->default(0);
            $table->json('risk_reasons')->nullable();
            $table->index(['source_ip', 'created_at']);
            $table->index(['phone', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['source_ip', 'created_at']);
            $table->dropIndex(['phone', 'created_at']);
            $table->dropColumn(['source_ip', 'risk_score', 'risk_reasons']);
        });
    }
};
