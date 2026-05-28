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

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active');
            }
            if (!Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->nullable();
            }
            if (!Schema::hasColumn('users', 'metadata')) {
                $table->json('metadata')->nullable();
            }
            if (!Schema::hasColumn('users', 'avatar_id')) {
                $table->unsignedBigInteger('avatar_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (!Schema::hasTable('identity_profiles')) {
            Schema::create('identity_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('username')->nullable()->unique();
                $table->string('phone')->nullable()->unique();
                $table->unsignedBigInteger('avatar_id')->nullable();
                $table->string('status')->default('active');
                $table->string('timezone')->default('UTC');
                $table->string('locale')->default('en');
                $table->boolean('two_factor_enabled')->default(false);
                $table->text('two_factor_secret')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }
}
