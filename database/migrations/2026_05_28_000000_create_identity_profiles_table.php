<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $usersTable = config('identity.tables.users', env('IDENTITY_USERS_TABLE', 'users'));
        $profilesTable = config('identity.tables.profiles', env('IDENTITY_PROFILES_TABLE', 'identity_profiles'));

        Schema::create($profilesTable, function (Blueprint $table) use ($usersTable) {
            $table->id();
            $table->foreignId('user_id')->constrained($usersTable)->cascadeOnDelete();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->unsignedBigInteger('avatar_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('locale')->default('en');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('remember_me')->default(false);
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        $profilesTable = config('identity.tables.profiles', env('IDENTITY_PROFILES_TABLE', 'identity_profiles'));
        Schema::dropIfExists($profilesTable);
    }
};
