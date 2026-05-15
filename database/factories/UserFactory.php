<?php

namespace Modules\Identity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Identity\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => 'active',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ];
    }
}