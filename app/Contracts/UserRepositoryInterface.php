<?php

namespace Modules\Identity\Contracts;

use Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function getAll(?int $perPage = null): LengthAwarePaginator|Collection;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUuid(string $uuid): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): bool;

    public function delete(User $user): bool;

    public function search(string $term, ?string $status = null, ?int $perPage = null): LengthAwarePaginator|Collection;
}