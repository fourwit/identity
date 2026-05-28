<?php

namespace Modules\Identity\Support;

class IdentityConfig
{
    public static function mode(): string
    {
        return config('identity.mode', 'shared');
    }

    public static function isOwnedMode(): bool
    {
        return self::mode() === 'owned';
    }

    public static function userModelClass(): string
    {
        return config('identity.models.user', \Modules\Identity\Models\User::class);
    }

    public static function usersTable(): string
    {
        return config('identity.tables.users', 'users');
    }
}
