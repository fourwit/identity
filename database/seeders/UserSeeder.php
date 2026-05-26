<?php

namespace Modules\Identity\Database\Seeders;
use Modules\Identity\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        User::updateOrCreate(
            ['email' => config('identity.branding.admin_email', 'admin@fourwit.com')],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => bcrypt('password'),
                'status' => 'active',
                'email_verified_at' => now(),
                'timezone' => 'UTC',
                'locale' => 'en',
            ]
        );

        // Create a sample customer user (optional)
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Customer',
                'first_name' => 'John',
                'last_name' => 'Customer',
                'password' => bcrypt('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('User seeder completed successfully!');
    }
}