<?php

namespace Modules\Identity\Observers;

use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Events\UserSuspended;
use Modules\Identity\Events\UserActivated;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(Model $user): void
    {
        // Auto-generate UUID if enabled in config
        if (config('identity.features.uuid', false) && empty($user->uuid)) {
            $user->uuid = Str::uuid()->toString();
        }

        // Set default status if not provided

        // die($user->status);
        if (empty($user->status) || $user->status === null || $user->status === '') {
            $defaultStatus = config('identity.user.default_status', UserStatus::ACTIVE);
            $user->status = UserStatus::tryFrom($defaultStatus);
        }

        // Lowercase email
        if (!empty($user->email)) {
            $user->email = strtolower($user->email);
        }

        // Lowercase username
        if (!empty($user->username)) {
            $user->username = strtolower($user->username);
        }

        // Set default metadata if empty
        if (empty($user->metadata)) {
            $user->metadata = [];
        }
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(Model $user): void
    {
        // Lowercase email if changed
        if ($user->isDirty('email') && !empty($user->email)) {
            $user->email = strtolower($user->email);
        }

        // Lowercase username if changed
        if ($user->isDirty('username') && !empty($user->username)) {
            $user->username = strtolower($user->username);
        }

        // FIX: Handle both Enum and string
        if ($user->isDirty('status')) {
            $newStatus = $user->status instanceof UserStatus 
                ? $user->status->value 
                : $user->status;

            if ($newStatus === 'suspended') {
                event(new UserSuspended($user));
            } elseif ($newStatus === 'active' && $user->getOriginal('status') === 'suspended') {
                event(new UserActivated($user));
            }
        }
    }
}
