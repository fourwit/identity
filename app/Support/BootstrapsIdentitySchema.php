<?php

namespace Modules\Identity\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Identity\Models\User;

trait BootstrapsIdentitySchema
{
    protected function bootstrapIdentitySchemaForTests(): void
    {
        config([
            'identity.mode' => 'owned',
            'identity.models.user' => User::class,
            'identity.tables.users' => 'users',
            'identity.tables.profiles' => 'identity_profiles',
        ]);

        if (!Schema::hasTable('identity_profiles')) {
            Schema::create('identity_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
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
            });
        }
    }
}
