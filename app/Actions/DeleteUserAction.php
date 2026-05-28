<?php

namespace Modules\Identity\Actions;

use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Events\UserSuspended;

use Modules\Identity\Services\ActivityLogger;
use Modules\Identity\Contracts\UserRepositoryInterface;

use Modules\Identity\Exceptions\UserNotFoundException;
use Modules\Identity\Exceptions\CannotDeleteUserException;
use Illuminate\Database\Eloquent\Model;

class DeleteUserAction
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function execute(Model $user, string $source = 'web'): void
    {
        $userId = $user->id;
        $userName = $user->name;

        if ($user->status === UserStatus::ACTIVE && $user->name === "Super Admin") {
            throw new CannotDeleteUserException('Cannot delete the main admin user');
        }

        // Log activity
        ActivityLogger::log(
            "Deleted user: {$userName}",
            null,
            ['deleted_user_id' => $userId, 'name' => $userName],
            'deleted',
            $source
        );

        if ($user->status === UserStatus::ACTIVE) {
            event(new UserSuspended($user, 'Account deleted'));
        }

        // $user->delete();
        $this->repository->delete($user);

        event(new UserDeleted($userId, $userName));
    }
}
