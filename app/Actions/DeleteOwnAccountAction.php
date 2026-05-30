<?php

namespace Modules\Identity\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Events\AccountDeleted;
use Modules\Identity\Exceptions\InvalidCurrentPasswordException;

class DeleteOwnAccountAction
{
    public function __construct(
        protected DeleteUserAction $deleteUserAction
    ) {}

    public function execute(Model $user, string $currentPassword, string $source = 'web'): void
    {
        if (!Hash::check($currentPassword, (string) $user->password)) {
            throw new InvalidCurrentPasswordException();
        }

        $id = $user->getKey();
        $name = $user->name ?? null;
        $email = $user->email ?? null;

        $this->deleteUserAction->execute($user, $source);

        event(new AccountDeleted((string) $id, $name, $email, $source));
    }
}
