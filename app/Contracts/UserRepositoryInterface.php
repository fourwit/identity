<?php

namespace Modules\Identity\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    public function getAll(?int $perPage = null): LengthAwarePaginator|Collection;

    public function userModel(): string;

    public function userQuery(): Builder;

    public function findById(int $id): ?Model;

    public function findByEmail(string $email): ?Model;

    public function findByUuid(string $uuid): ?Model;

    public function create(array $data): Model;

    public function update(Model $user, array $data): bool;

    public function delete(Model $user): bool;

    public function search(?string $term, ?string $status = null, ?int $perPage = null, ?string $sortBy = null, ?string $sortDir = null): LengthAwarePaginator|Collection;
}
