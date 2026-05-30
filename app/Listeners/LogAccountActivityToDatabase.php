<?php

namespace Modules\Identity\Listeners;

use Modules\Identity\Events\AccountDeleted;
use Modules\Identity\Events\ProfileUpdated;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Events\UserPasswordUpdated;
use Modules\Identity\Services\ActivityLogger;

class LogAccountActivityToDatabase
{
    public function handle(object $event): void
    {
        if ($event instanceof ProfileUpdated) {
            $safeChanges = collect($event->changes)
                ->except(['password', 'current_password', 'password_confirmation', 'two_factor_secret'])
                ->all();

            ActivityLogger::log(
                'Profile updated',
                $event->user,
                ['changed_fields' => array_keys($safeChanges)],
                'profile_updated',
                $event->source ?? 'web'
            );
            return;
        }

        if ($event instanceof UserPasswordUpdated) {
            ActivityLogger::log(
                'Password updated',
                $event->user,
                ['changed_fields' => ['password']],
                'password_updated',
                $event->source ?? 'web'
            );
            return;
        }

        if ($event instanceof AccountDeleted) {
            ActivityLogger::log(
                'Own account deleted',
                null,
                ['deleted_user_id' => (int) $event->userId, 'name' => $event->name, 'email' => $event->email],
                'account_deleted',
                $event->source ?? 'web'
            );
            return;
        }

        if ($event instanceof UserDeleted) {
            ActivityLogger::log(
                "Deleted user: {$event->userName}",
                null,
                ['deleted_user_id' => $event->userId, 'name' => $event->userName],
                'deleted',
                $event->source ?? 'web'
            );
        }
    }
}
