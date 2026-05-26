<?php

namespace Modules\Identity\Repositories;

use Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Identity\Exceptions\UserNotFoundException;

class UserRepository implements UserRepositoryInterface
{
    public function getAll(?int $perPage = null): LengthAwarePaginator|Collection
    {
        $query = User::latest();
        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }
    
    public function findByIdOrFail(int $id): User
    {
        $user = $this->findById($id);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByUuid(string $uuid): ?User
    {
        return User::where('uuid', $uuid)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    
    public function search(?string $term, ?string $status = null, ?int $perPage = null) : LengthAwarePaginator|Collection
    {
        $query = User::query();

        // Use scopeSearch if term provided
        if (!empty($term)) {
            $query->search($term);  // ← Use scope
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $perPage 
            ? $query->latest()->paginate($perPage) 
            : $query->latest()->get();
    }
}