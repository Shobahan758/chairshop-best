<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAccountFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->name(), 'email' => fake()->safeEmail(), 'phone' => '01700000000', 'referral_code' => fake()->unique()->regexify('[a-z0-9]{16}')];
    }
}
