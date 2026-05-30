<?php

namespace Modules\Identity\Actions;

use Modules\Identity\Events\UserDeleted;

use Modules\Identity\Contracts\UserRepositoryInterface;

use Modules\Identity\Exceptions\UserNotFoundException;
use Modules\Identity\Exceptions\CannotDeleteUserException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeleteUserAction
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function execute(Model $user, string $source = 'web'): void
    {
        $userId = $user->id;
        $userName = $user->name;
        $normalizedName = strtolower(trim((string) $userName));
        $userEmail = strtolower(trim((string) ($user->email ?? '')));
        $userUuid = trim((string) ($user->uuid ?? ''));

        if (config('identity.protection.enabled', true)) {
            $configuredUuid = trim((string) config('identity.protection.super_admin_uuid', ''));
            $configuredEmail = strtolower(trim((string) config('identity.protection.super_admin_email', '')));
            $configuredName = strtolower(trim((string) config('identity.protection.super_admin_name', 'super admin')));

            $isProtectedUser =
                ($configuredUuid !== '' && $userUuid !== '' && $userUuid === $configuredUuid) ||
                ($configuredEmail !== '' && $userEmail !== '' && $userEmail === $configuredEmail) ||
                ($configuredName !== '' && $normalizedName === $configuredName);

            if ($isProtectedUser) {
                throw new CannotDeleteUserException('Cannot delete the main admin user');
            }
        }

        $strategy = (string) config('identity.deletion.strategy', 'safe');
        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($user), true);

        if ($strategy === 'safe' && !$usesSoftDeletes) {
            throw new CannotDeleteUserException('Safe deletion strategy requires SoftDeletes on the user model.');
        }

        $this->repository->delete($user);

        event(new UserDeleted($userId, $userName, $source));
    }
}
