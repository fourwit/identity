<?php

namespace Modules\Identity\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Actions\CreateUserAction;
use Modules\Identity\Actions\DeleteOwnAccountAction;
use Modules\Identity\Actions\DeleteUserAction;
use Modules\Identity\Actions\UpdateAccountProfileAction;
use Modules\Identity\Actions\UpdateUserAction;
use Modules\Identity\Actions\UpdateUserPasswordAction;
use Modules\Identity\Contracts\IdentityContract;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\DTOs\UserData;
use Modules\Identity\Models\ActivityLog;
use Modules\Identity\Models\IdentityProfile;

class IdentityManager implements IdentityContract
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function userModel(): string
    {
        return $this->repository->userModel();
    }

    public function userQuery(): Builder
    {
        return $this->repository->userQuery();
    }

    public function updateAccountProfile(Model $user, array $data, string $source = 'web'): Model
    {
        return app(UpdateAccountProfileAction::class)->execute($user, $data, $source);
    }

    public function updateUserPassword(Model $user, string $currentPassword, string $newPassword, string $source = 'web'): Model
    {
        return app(UpdateUserPasswordAction::class)->execute($user, $currentPassword, $newPassword, $source);
    }

    public function deleteOwnAccount(Model $user, string $currentPassword, string $source = 'web'): void
    {
        app(DeleteOwnAccountAction::class)->execute($user, $currentPassword, $source);
    }

    public function findUserById(int $id): ?Model
    {
        return $this->repository->findById($id);
    }

    public function findById(int $id): ?Model
    {
        return $this->findUserById($id);
    }

    public function findUserByEmail(string $email): ?Model
    {
        return $this->repository->findByEmail($email);
    }

    public function findByEmail(string $email): ?Model
    {
        return $this->findUserByEmail($email);
    }

    public function findUserByUuid(string $uuid): ?Model
    {
        return $this->repository->findByUuid($uuid);
    }

    public function findByUuid(string $uuid): ?Model
    {
        return $this->findUserByUuid($uuid);
    }

    public function allUsers(?int $perPage = null)
    {
        return $this->repository->getAll($perPage);
    }

    public function getAll(?int $perPage = null)
    {
        return $this->allUsers($perPage);
    }

    public function searchUsers(?string $term, ?string $status = null, ?int $perPage = null)
    {
        return $this->repository->search($term, $status, $perPage);
    }

    public function search(?string $term, ?string $status = null, ?int $perPage = null)
    {
        return $this->searchUsers($term, $status, $perPage);
    }

    public function activeUsers(?int $perPage = null)
    {
        return $this->repository->search(null, 'active', $perPage);
    }

    public function getActiveUsers(?int $perPage = null)
    {
        return $this->activeUsers($perPage);
    }

    public function createUser(array $data): Model
    {
        return app(CreateUserAction::class)->execute(
            $this->arrayToUserData($data),
            (string) ($data['source'] ?? 'facade')
        );
    }

    public function updateUser(Model $user, array $data): bool
    {
        app(UpdateUserAction::class)->execute(
            $user,
            $this->arrayToUserData($data),
            (string) ($data['source'] ?? 'facade')
        );

        return true;
    }

    public function deleteUser(Model $user): bool
    {
        app(DeleteUserAction::class)->execute($user, 'facade');

        return true;
    }

    public function activityLogsCount(): int
    {
        return ActivityLog::count();
    }

    public function setMetadata(Model $user, string $key, mixed $value): bool
    {
        $profile = IdentityProfile::firstOrCreate(
            ['user_id' => $user->getKey()],
            ['metadata' => []]
        );

        $metadata = (array) ($profile->metadata ?? []);
        $metadata[$key] = $value;

        $profile->metadata = $metadata;
        $saved = $profile->save();

        if ($saved) {
            $user->setAttribute('metadata', $metadata);
            $user->syncOriginal();
        }

        return $saved;
    }

    public function getMetadata(Model $user, string $key, mixed $default = null): mixed
    {
        $profile = IdentityProfile::where('user_id', $user->getKey())->first();

        if (!$profile || !is_array($profile->metadata)) {
            return $default;
        }

        $metadata = $profile->metadata;

        return array_key_exists($key, $metadata) ? $metadata[$key] : $default;
    }

    public function hasMetadata(Model $user, string $key): bool
    {
        $profile = IdentityProfile::where('user_id', $user->getKey())->first();

        if (!$profile || !is_array($profile->metadata)) {
            return false;
        }

        return array_key_exists($key, $profile->metadata);
    }

    public function forgetMetadata(Model $user, string $key): bool
    {
        $profile = IdentityProfile::where('user_id', $user->getKey())->first();

        if (!$profile || !is_array($profile->metadata)) {
            return false;
        }

        $metadata = $profile->metadata;

        if (!array_key_exists($key, $metadata)) {
            return false;
        }

        unset($metadata[$key]);
        $profile->metadata = $metadata;

        $saved = $profile->save();

        if ($saved) {
            $user->setAttribute('metadata', $metadata);
            $user->syncOriginal();
        }

        return $saved;
    }

    protected function arrayToUserData(array $data): UserData
    {
        return new UserData(
            name: $data['name'] ?? null,
            firstName: $data['first_name'] ?? $data['firstName'] ?? null,
            lastName: $data['last_name'] ?? $data['lastName'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            username: $data['username'] ?? null,
            status: is_string($data['status'] ?? null) ? $data['status'] : (is_object($data['status'] ?? null) && method_exists($data['status'], 'value') ? $data['status']->value : null),
            password: $data['password'] ?? null,
        );
    }
}
