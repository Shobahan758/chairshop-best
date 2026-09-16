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
        Schema::create('tracking_settings', function (Blueprint $table) {
            $table->id();
            $table->string('meta_pixel_id')->nullable();
            $table->string('google_tag_id')->nullable();
            $table->string('tiktok_pixel_id')->nullable();
            $table->string('pinterest_tag_id')->nullable();
            $table->string('snapchat_pixel_id')->nullable();
            $table->boolean('tracking_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_settings');
    }
};
