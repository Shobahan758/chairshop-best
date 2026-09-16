<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('referral_code', 32)->unique();
            $table->unsignedInteger('referral_visits')->default(0);
            $table->json('addresses')->nullable();
            $table->json('wishlist')->nullable();
            $table->json('wallet_entries')->nullable();
            $table->json('loyalty_entries')->nullable();
            $table->timestamp('inbox_read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_account_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 160);
            $table->string('status', 20)->default('open');
            $table->json('messages');
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referrer_account_id')->nullable()->constrained('customer_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_account_id');
            $table->dropConstrainedForeignId('referrer_account_id');
        });
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('customer_accounts');
    }
};
