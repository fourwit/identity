<?php

namespace Modules\Identity\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Identity\Exceptions\UserNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Support\IdentityConfig;
use Modules\Identity\Models\IdentityProfile;
use Illuminate\Support\Facades\Schema;

class UserRepository implements UserRepositoryInterface
{
    protected array $sharedModeCoreFields = [
        'name',
        'email',
        'password',
    ];

    protected array $sharedModeProfileFields = [
        'first_name',
        'last_name',
        'phone',
        'username',
        'status',
        'avatar_id',
        'timezone',
        'locale',
        'two_factor_enabled',
        'two_factor_secret',
        'metadata',
    ];

    protected function userModelClass(): string
    {
        return IdentityConfig::userModelClass();
    }

    protected function query()
    {
        $modelClass = $this->userModelClass();
        return $modelClass::query();
    }

    public function getAll(?int $perPage = null): LengthAwarePaginator|Collection
    {
        $query = $this->query()->latest();
        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function findById(int $id): ?Model
    {
        $modelClass = $this->userModelClass();
        $user = $modelClass::find($id);

        return $user ? $this->hydrateSharedProfile($user) : null;
    }
    
    public function findByIdOrFail(int $id): Model
    {
        $user = $this->findById($id);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function findByEmail(string $email): ?Model
    {
        $user = $this->query()->where('email', $email)->first();
        return $user ? $this->hydrateSharedProfile($user) : null;
    }

    public function findByUuid(string $uuid): ?Model
    {
        if (!Schema::hasColumn(IdentityConfig::usersTable(), 'uuid')) {
            return null;
        }

        $user = $this->query()->where('uuid', $uuid)->first();
        return $user ? $this->hydrateSharedProfile($user) : null;
    }

    public function create(array $data): Model
    {
        $modelClass = $this->userModelClass();
        if (IdentityConfig::isOwnedMode()) {
            return $modelClass::create($data);
        }

        $coreData = array_intersect_key($data, array_flip($this->sharedModeCoreFields));
        $profileData = array_intersect_key($data, array_flip($this->sharedModeProfileFields));

        $user = $modelClass::create($coreData);

        if (!empty($profileData)) {
            IdentityProfile::updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
        }

        return $this->hydrateSharedProfile($user->refresh());
    }

    public function update(Model $user, array $data): bool
    {
        if (IdentityConfig::isOwnedMode()) {
            return $user->update($data);
        }

        $coreData = array_intersect_key($data, array_flip($this->sharedModeCoreFields));
        $profileData = array_intersect_key($data, array_flip($this->sharedModeProfileFields));

        $updated = true;

        if (!empty($coreData)) {
            $updated = $user->update($coreData) && $updated;
        }

        if (!empty($profileData)) {
            IdentityProfile::updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
        }

        $this->hydrateSharedProfile($user->refresh());

        return $updated;
    }

    public function delete(Model $user): bool
    {
        return $user->delete();
    }

    
    public function search(?string $term, ?string $status = null, ?int $perPage = null) : LengthAwarePaginator|Collection
    {
        $query = $this->query();

        // Use scopeSearch if term provided
        if (!empty($term)) {
            $query->search($term);  // ← Use scope
        }

        if (!empty($status)) {
            if (IdentityConfig::isOwnedMode()) {
                $query->where('status', $status);
            } else {
                $query->whereIn('id', IdentityProfile::query()->where('status', $status)->pluck('user_id'));
            }
        }

        return $perPage 
            ? $query->latest()->paginate($perPage) 
            : $query->latest()->get();
    }

    protected function hydrateSharedProfile(Model $user): Model
    {
        if (IdentityConfig::isOwnedMode()) {
            return $user;
        }

        $profile = IdentityProfile::query()->where('user_id', $user->id)->first();

        if (!$profile) {
            return $user;
        }

        foreach ($this->sharedModeProfileFields as $field) {
            if (array_key_exists($field, $profile->getAttributes())) {
                $user->setAttribute($field, $profile->getAttribute($field));
            }
        }

        return $user;
    }
}
