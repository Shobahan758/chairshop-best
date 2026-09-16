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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('just_for_you')->default(false)->after('featured');
            $table->boolean('office_essential')->default(false)->after('just_for_you');
            $table->boolean('gaming_pick')->default(false)->after('office_essential');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['just_for_you', 'office_essential', 'gaming_pick']);
        });
    }
};
