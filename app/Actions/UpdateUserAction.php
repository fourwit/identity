<?php

namespace Modules\Identity\Actions;

use Modules\Identity\DTOs\UserData;
use Modules\Identity\Models\User;
use Modules\Identity\Events\UserUpdated;
use Modules\Identity\Services\ActivityLogger;

class UpdateUserAction
{
    public function execute(User $user, UserData $data, string $source = 'web'): User
    {
        $oldData = $user->only(['name', 'email', 'status']);

        if ($data->status === null) {
            $data->status = $user->status;
        }

        $updateData = $data->toUpdateArray();

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Log activity
        ActivityLogger::log(
            "Updated user: {$user->name}",
            $user,
            ['old' => $oldData, 'new' => $user->only(['name', 'email', 'status'])],
            'updated',
            $source
        );

        event(new UserUpdated($user, $user->getChanges()));

        return $user;
    }
}