<?php

namespace Modules\Identity\Database\Seeders;
use Modules\Identity\Models\User;
use Modules\Identity\Models\IdentityProfile;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => config('identity.branding.admin_email', 'admin@fourwit.com')],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        IdentityProfile::updateOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'status' => 'active',
                'timezone' => 'UTC',
                'locale' => 'en',
            ]
        );

        // Create a sample customer user (optional)
        $customer = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Customer',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        IdentityProfile::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'first_name' => 'John',
                'last_name' => 'Customer',
                'status' => 'active',
            ]
        );

        $this->command->info('User seeder completed successfully!');
    }
}
