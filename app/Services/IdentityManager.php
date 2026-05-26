<?php

namespace Modules\Identity\Services;

use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\DTOs\UserData;
use Modules\Identity\Models\User;

class IdentityManager
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * Find user by UUID
     */
    public function findByUuid(string $uuid): ?User
    {
        return $this->repository->findByUuid($uuid);
    }

    /**
     * Get all users (paginated)
     */
    public function getAll(?int $perPage = null)
    {
        return $this->repository->getAll($perPage);
    }

    /**
     * Search users
     */
    public function search(?string $term, ?string $status = null, ?int $perPage = null)
    {
        return $this->repository->search($term, $status, $perPage);
    }

    /**
     * Get active users only
     */
    public function getActiveUsers(?int $perPage = null)
    {
        return $this->repository->search(null, 'active', $perPage);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        return $this->repository->create($data);
    }

    /**
     * Update a user
     */
    public function updateUser(User $user, array $data): bool
    {
        return $this->repository->update($user, $data);
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user): bool
    {
        return $this->repository->delete($user);
    }
}