<?php

namespace Modules\Identity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Identity\Models\ActivityLog;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'log_name'    => 'user',
            'description' => fake()->sentence(),
            'event'       => fake()->randomElement(['created', 'updated', 'deleted']),
            'source'      => fake()->randomElement(['web', 'api']),
            'ip_address'  => fake()->ipv4(),
            'user_agent'  => fake()->userAgent(),
            'properties'  => [],
        ];
    }
}