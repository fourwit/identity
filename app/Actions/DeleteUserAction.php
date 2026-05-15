<?php

namespace Modules\Identity\Actions;

use Modules\Identity\Models\User;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Services\ActivityLogger;

class DeleteUserAction
{
    public function execute(User $user, string $source = 'web'): void
    {
        $userId = $user->id;
        $userName = $user->name;

        // Log activity
        ActivityLogger::log(
            "Deleted user: {$userName}",
            null,
            ['deleted_user_id' => $userId, 'name' => $userName],
            'deleted',
            $source
        );

        $user->delete();

        event(new UserDeleted($userId, $userName));
    }
}