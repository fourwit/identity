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
use Modules\Identity\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;

class UserRepository implements UserRepositoryInterface
{
    protected array $sharedModeCoreFields = [
        'name',
        'email',
        'email_verified_at',
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
        if ($perPage) {
            $paginator = $query->paginate($perPage);
            $paginator->getCollection()->transform(fn ($user) => $this->hydrateSharedProfile($user));
            return $paginator;
        }

        return $query->get()->map(fn ($user) => $this->hydrateSharedProfile($user));
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
            $updated = $user->forceFill($coreData)->save() && $updated;
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

    
    public function search(?string $term, ?string $status = null, ?int $perPage = null, ?string $sortBy = null, ?string $sortDir = null) : LengthAwarePaginator|Collection
    {
        $query = $this->query();

        $this->applySearch($query, $term);
        $this->applyStatus($query, $status);
        $this->applySort($query, $sortBy, $sortDir);

        return $this->hydrateResults($query, $perPage);
    }

    protected function applySearch(Builder $query, ?string $term): void
    {
        if (empty($term)) {
            return;
        }

        [$hostFields, $profileFields] = $this->resolveSearchFields();

        if (empty($hostFields) && empty($profileFields)) {
            $hostFields = $this->fallbackSearchFields();
        }

        $query->where(function (Builder $builder) use ($term, $hostFields, $profileFields) {
            foreach ($hostFields as $index => $field) {
                if ($index === 0) {
                    $builder->where($field, 'like', "%{$term}%");
                } else {
                    $builder->orWhere($field, 'like', "%{$term}%");
                }
            }

            if (!IdentityConfig::isOwnedMode() && !empty($profileFields)) {
                $builder->orWhereIn('id', function ($subQuery) use ($term, $profileFields) {
                    $this->applyProfileSearchSubquery($subQuery, $term, $profileFields);
                });
            }
        });
    }

    protected function applyStatus(Builder $query, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        if (IdentityConfig::isOwnedMode()) {
            $query->where('status', $status);
            return;
        }

        $query->whereIn('id', function ($subQuery) use ($status) {
            $subQuery->from(config('identity.tables.profiles', 'identity_profiles'))
                ->select('user_id')
                ->where('status', $status);
        });
    }

    protected function applyProfileSearchSubquery($subQuery, string $term, array $profileFields): void
    {
        $subQuery->from(config('identity.tables.profiles', 'identity_profiles'))
            ->select('user_id')
            ->where(function ($profileQuery) use ($profileFields, $term) {
                foreach ($profileFields as $index => $field) {
                    if ($index === 0) {
                        $profileQuery->where($field, 'like', "%{$term}%");
                    } else {
                        $profileQuery->orWhere($field, 'like', "%{$term}%");
                    }
                }
            });
    }

    protected function resolveSearchFields(): array
    {
        $searchableFields = (array) config('identity.user.searchable_fields', ['name', 'email', 'phone', 'username']);
        $usersTable = IdentityConfig::usersTable();

        $hostFields = [];
        $profileFields = [];

        foreach ($searchableFields as $field) {
            if (in_array($field, ['phone', 'username', 'first_name', 'last_name'], true)) {
                if (IdentityConfig::isOwnedMode() && Schema::hasColumn($usersTable, $field)) {
                    $hostFields[] = $field;
                }

                if (!IdentityConfig::isOwnedMode()) {
                    $profileFields[] = $field;
                }

                continue;
            }

            if (Schema::hasColumn($usersTable, $field)) {
                $hostFields[] = $field;
            }
        }

        return [$hostFields, $profileFields];
    }

    protected function fallbackSearchFields(): array
    {
        $usersTable = IdentityConfig::usersTable();
        $fallbackFields = [];

        if (Schema::hasColumn($usersTable, 'name')) {
            $fallbackFields[] = 'name';
        }

        if (Schema::hasColumn($usersTable, 'email')) {
            $fallbackFields[] = 'email';
        }

        return $fallbackFields;
    }

    protected function hydrateResults(Builder $query, ?int $perPage): LengthAwarePaginator|Collection
    {
        if ($perPage) {
            $paginator = $query->paginate($perPage);
            $paginator->getCollection()->transform(fn ($user) => $this->hydrateSharedProfile($user));
            return $paginator;
        }

        return $query->get()->map(fn ($user) => $this->hydrateSharedProfile($user));
    }

    protected function applySort(Builder $query, ?string $sortBy, ?string $sortDir): void
    {
        $direction = strtolower((string) $sortDir) === 'asc' ? 'asc' : 'desc';
        $sortBy = (string) ($sortBy ?? 'created_at');

        $allowedHostSortColumns = ['id', 'name', 'email', 'last_login_at', 'created_at'];

        if (in_array($sortBy, $allowedHostSortColumns, true) && Schema::hasColumn(IdentityConfig::usersTable(), $sortBy)) {
            $query->orderBy($sortBy, $direction);
            return;
        }

        if (!IdentityConfig::isOwnedMode() && in_array($sortBy, ['phone', 'status'], true)) {
            $profilesTable = config('identity.tables.profiles', 'identity_profiles');
            $usersTable = IdentityConfig::usersTable();
            $query->leftJoin($profilesTable, "{$profilesTable}.user_id", '=', "{$usersTable}.id")
                ->select("{$usersTable}.*")
                ->orderBy("{$profilesTable}.{$sortBy}", $direction);
            return;
        }

        if (IdentityConfig::isOwnedMode() && in_array($sortBy, ['phone', 'status'], true) && Schema::hasColumn(IdentityConfig::usersTable(), $sortBy)) {
            $query->orderBy($sortBy, $direction);
            return;
        }

        if (Schema::hasColumn(IdentityConfig::usersTable(), 'created_at')) {
            $query->orderBy('created_at', 'desc');
        }
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
                $value = $profile->getAttribute($field);
                if ($field === 'status') {
                    $value = UserStatus::tryFrom((string) $value) ?? UserStatus::ACTIVE;
                }
                $user->setAttribute($field, $value);
                // Prevent shared profile attributes from being treated as dirty
                // on the host user model (which may not have these columns).
                $user->syncOriginalAttribute($field);
            }
        }

        return $user;
    }
}
