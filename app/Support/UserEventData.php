<?php

namespace Modules\Identity\Support;

use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Enums\UserStatus;

class UserEventData
{
    public static function id(Model $user): int
    {
        return (int) $user->getKey();
    }

    public static function uuid(Model $user): ?string
    {
        $uuid = $user->uuid ?? $user->identityProfile?->uuid ?? null;

        if ($uuid === null || $uuid === '') {
            return null;
        }

        return (string) $uuid;
    }

    public static function statusValue(Model $user): string
    {
        $status = $user->status ?? null;

        if ($status instanceof UserStatus) {
            return $status->value;
        }

        if ($status !== null && $status !== '') {
            return (string) $status;
        }

        return (string) config('identity.user.default_status', 'active');
    }
}
