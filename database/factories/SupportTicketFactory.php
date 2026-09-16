<?php

namespace Database\Factories;

use App\Models\CustomerAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportTicketFactory extends Factory
{
    public function definition(): array
    {
        return ['customer_account_id' => CustomerAccount::factory(), 'subject' => fake()->sentence(), 'status' => 'open', 'messages' => [['author' => 'customer', 'body' => fake()->paragraph(), 'at' => now()->toIso8601String()]]];
    }
}
