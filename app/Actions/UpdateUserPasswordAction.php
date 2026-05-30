<?php

namespace Modules\Identity\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Events\UserPasswordUpdated;
use Modules\Identity\Exceptions\InvalidCurrentPasswordException;

class UpdateUserPasswordAction
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function execute(Model $user, string $currentPassword, string $newPassword, string $source = 'web'): Model
    {
        if (!Hash::check($currentPassword, (string) $user->password)) {
            throw new InvalidCurrentPasswordException();
        }

        $this->repository->update($user, [
            'password' => Hash::make($newPassword),
        ]);

        $fresh = $this->repository->findByIdOrFail((int) $user->getKey());
        event(new UserPasswordUpdated($fresh, $source));

        return $fresh;
    }
}
