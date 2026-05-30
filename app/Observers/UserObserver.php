<?php

namespace Modules\Identity\Observers;

use Illuminate\Database\Eloquent\Model;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(Model $user): void
    {
        // Lowercase email
        if (!empty($user->email)) {
            $user->email = strtolower($user->email);
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
    }
}
