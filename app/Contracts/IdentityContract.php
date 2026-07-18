<?php

namespace Modules\Identity\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface IdentityContract
{
    public function userModel(): string;

    public function userQuery(): Builder;

    public function findUserById(int $id): ?Model;

    public function findUserByEmail(string $email): ?Model;

    public function findUserByUuid(string $uuid): ?Model;

    public function allUsers(?int $perPage = null);

    public function searchUsers(?string $term, ?string $status = null, ?int $perPage = null);

    public function activeUsers(?int $perPage = null);

    public function createUser(array $data): Model;

    public function updateUser(Model $user, array $data): bool;

    public function deleteUser(Model $user): bool;

    public function activityLogsCount(): int;

    public function updateAccountProfile(Model $user, array $data, string $source = 'web'): Model;

    public function updateUserPassword(Model $user, string $currentPassword, string $newPassword, string $source = 'web'): Model;

    public function deleteOwnAccount(Model $user, string $currentPassword, string $source = 'web'): void;

    public function setMetadata(Model $user, string $key, mixed $value): bool;

    public function getMetadata(Model $user, string $key, mixed $default = null): mixed;

    public function hasMetadata(Model $user, string $key): bool;

    public function forgetMetadata(Model $user, string $key): bool;
}
