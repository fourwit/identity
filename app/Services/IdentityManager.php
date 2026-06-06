<?php

namespace Modules\Identity\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Events\AccountDeleted;
use Modules\Identity\Events\ProfileUpdated;
use Modules\Identity\Events\UserPasswordUpdated;
use Modules\Identity\Exceptions\InvalidCurrentPasswordException;
use Modules\Identity\Models\ActivityLog;

class IdentityManager
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function userModel(): string
    {
        return $this->repository->userModel();
    }

    public function userQuery()
    {
        return $this->repository->userQuery();
    }

    public function updateAccountProfile(Model $user, array $data, string $source = 'web'): Model
    {
        $payload = $data;

        if (array_key_exists('email', $payload) && (string) $payload['email'] !== (string) $user->email) {
            $payload['email_verified_at'] = null;
        }

        $this->updateUser($user, $payload);
        $fresh = $this->repository->findByIdOrFail((int) $user->getKey());

        event(new ProfileUpdated($fresh, $payload, $source));

        return $fresh;
    }

    public function updateUserPassword(Model $user, string $currentPassword, string $newPassword, string $source = 'web'): Model
    {
        if (! Hash::check($currentPassword, (string) $user->password)) {
            throw new InvalidCurrentPasswordException();
        }

        $this->repository->update($user, [
            'password' => Hash::make($newPassword),
        ]);

        $fresh = $this->repository->findByIdOrFail((int) $user->getKey());
        event(new UserPasswordUpdated($fresh, $source));

        return $fresh;
    }

    public function deleteOwnAccount(Model $user, string $currentPassword, string $source = 'web'): void
    {
        if (! Hash::check($currentPassword, (string) $user->password)) {
            throw new InvalidCurrentPasswordException();
        }

        $id = $user->getKey();
        $name = $user->name ?? null;
        $email = $user->email ?? null;

        $this->deleteUser($user);

        event(new AccountDeleted((string) $id, $name, $email, $source));
    }

    /**
     * Find user by ID
     */
    public function findUserById(int $id): ?Model
    {
        return $this->repository->findById($id);
    }

    public function findById(int $id): ?Model
    {
        return $this->findUserById($id);
    }

    /**
     * Find user by email
     */
    public function findUserByEmail(string $email): ?Model
    {
        return $this->repository->findByEmail($email);
    }

    public function findByEmail(string $email): ?Model
    {
        return $this->findUserByEmail($email);
    }

    /**
     * Find user by UUID
     */
    public function findUserByUuid(string $uuid): ?Model
    {
        return $this->repository->findByUuid($uuid);
    }

    public function findByUuid(string $uuid): ?Model
    {
        return $this->findUserByUuid($uuid);
    }

    /**
     * Get all users (paginated)
     */
    public function allUsers(?int $perPage = null)
    {
        return $this->repository->getAll($perPage);
    }

    public function getAll(?int $perPage = null)
    {
        return $this->allUsers($perPage);
    }

    /**
     * Search users
     */
    public function searchUsers(?string $term, ?string $status = null, ?int $perPage = null)
    {
        return $this->repository->search($term, $status, $perPage);
    }

    public function search(?string $term, ?string $status = null, ?int $perPage = null)
    {
        return $this->searchUsers($term, $status, $perPage);
    }

    /**
     * Get active users only
     */
    public function activeUsers(?int $perPage = null)
    {
        return $this->repository->search(null, 'active', $perPage);
    }

    public function getActiveUsers(?int $perPage = null)
    {
        return $this->activeUsers($perPage);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Update a user
     */
    public function updateUser(Model $user, array $data): bool
    {
        return $this->repository->update($user, $data);
    }

    /**
     * Delete a user
     */
    public function deleteUser(Model $user): bool
    {
        return $this->repository->delete($user);
    }

    public function activityLogsCount(): int
    {
        return ActivityLog::count();
    }
}
