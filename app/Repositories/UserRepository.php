<?php

namespace Modules\Identity\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Exceptions\UserNotFoundException;
use Modules\Identity\Models\IdentityProfile;
use Modules\Identity\Support\IdentityConfig;

class UserRepository implements UserRepositoryInterface
{
    protected array $coreFields = [
        'name',
        'email',
        'email_verified_at',
        'password',
    ];

    protected array $profileFields = [
        'uuid',
        'first_name',
        'last_name',
        'phone',
        'username',
        'status',
        'avatar_id',
        'phone_verified_at',
        'timezone',
        'locale',
        'last_login_at',
        'last_login_ip',
        'remember_me',
        'two_factor_enabled',
        'two_factor_secret',
        'metadata',
    ];

    protected function userModelClass(): string
    {
        return IdentityConfig::userModelClass();
    }

    protected function query(): Builder
    {
        $modelClass = $this->userModelClass();
        return $modelClass::query();
    }

    public function getAll(?int $perPage = null): LengthAwarePaginator|Collection
    {
        $query = $this->query()->latest();

        if ($perPage) {
            $paginator = $query->paginate($perPage);
            $paginator->getCollection()->transform(fn ($user) => $this->hydrateIdentityProfile($user));
            return $paginator;
        }

        return $query->get()->map(fn ($user) => $this->hydrateIdentityProfile($user));
    }

    public function findById(int $id): ?Model
    {
        $modelClass = $this->userModelClass();
        $user = $modelClass::find($id);

        return $user ? $this->hydrateIdentityProfile($user) : null;
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
        return $user ? $this->hydrateIdentityProfile($user) : null;
    }

    public function findByUuid(string $uuid): ?Model
    {
        $profilesTable = config('identity.tables.profiles', 'identity_profiles');
        if (!Schema::hasColumn($profilesTable, 'uuid')) {
            return null;
        }

        $user = $this->query()->whereIn('id', function ($subQuery) use ($profilesTable, $uuid) {
            $subQuery->from($profilesTable)
                ->select('user_id')
                ->where('uuid', $uuid);
        })->first();

        return $user ? $this->hydrateIdentityProfile($user) : null;
    }

    public function create(array $data): Model
    {
        $modelClass = $this->userModelClass();

        $coreData = array_intersect_key($data, array_flip($this->coreFields));
        $profileData = array_intersect_key($data, array_flip($this->profileFields));

        $user = $modelClass::create($coreData);

        IdentityProfile::updateOrCreate(
            ['user_id' => $user->id],
            $this->normalizeProfileData($profileData)
        );

        return $this->hydrateIdentityProfile($user->refresh());
    }

    public function update(Model $user, array $data): bool
    {
        $coreData = array_intersect_key($data, array_flip($this->coreFields));
        $profileData = array_intersect_key($data, array_flip($this->profileFields));

        $updated = true;

        if (!empty($coreData)) {
            $updated = $user->forceFill($coreData)->save() && $updated;
        }

        if (!empty($profileData)) {
            IdentityProfile::updateOrCreate(
                ['user_id' => $user->id],
                $this->normalizeProfileData($profileData)
            );
        }

        $this->hydrateIdentityProfile($user->refresh());

        return $updated;
    }

    public function delete(Model $user): bool
    {
        return $user->delete();
    }

    public function search(?string $term, ?string $status = null, ?int $perPage = null, ?string $sortBy = null, ?string $sortDir = null): LengthAwarePaginator|Collection
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

            if (!empty($profileFields)) {
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
            if (in_array($field, ['uuid', 'phone', 'username', 'first_name', 'last_name', 'status'], true)) {
                $profileFields[] = $field;
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
            $paginator->getCollection()->transform(fn ($user) => $this->hydrateIdentityProfile($user));
            return $paginator;
        }

        return $query->get()->map(fn ($user) => $this->hydrateIdentityProfile($user));
    }

    protected function applySort(Builder $query, ?string $sortBy, ?string $sortDir): void
    {
        $direction = strtolower((string) $sortDir) === 'asc' ? 'asc' : 'desc';
        $sortBy = (string) ($sortBy ?? 'created_at');

        $allowedHostSortColumns = ['id', 'name', 'email', 'created_at'];

        if (in_array($sortBy, $allowedHostSortColumns, true) && Schema::hasColumn(IdentityConfig::usersTable(), $sortBy)) {
            $query->orderBy($sortBy, $direction);
            return;
        }

        if (in_array($sortBy, ['username', 'phone', 'status', 'last_login_at'], true)) {
            $profilesTable = config('identity.tables.profiles', 'identity_profiles');
            $usersTable = IdentityConfig::usersTable();
            $query->leftJoin($profilesTable, "{$profilesTable}.user_id", '=', "{$usersTable}.id")
                ->select("{$usersTable}.*")
                ->orderBy("{$profilesTable}.{$sortBy}", $direction);
            return;
        }

        if (Schema::hasColumn(IdentityConfig::usersTable(), 'created_at')) {
            $query->orderBy('created_at', 'desc');
        }
    }

    protected function hydrateIdentityProfile(Model $user): Model
    {
        $profile = IdentityProfile::query()->where('user_id', $user->id)->first();

        if (!$profile) {
            return $user;
        }

        foreach ($this->profileFields as $field) {
            if (array_key_exists($field, $profile->getAttributes())) {
                $value = $profile->getAttribute($field);
                if ($field === 'status') {
                    if ($value instanceof UserStatus) {
                        $value = $value;
                    } else {
                        $value = UserStatus::tryFrom((string) $value) ?? UserStatus::ACTIVE;
                    }
                }
                $user->setAttribute($field, $value);
            }
        }

        $user->syncOriginal();

        return $user;
    }

    protected function normalizeProfileData(array $profileData): array
    {
        if (array_key_exists('status', $profileData) && $profileData['status'] instanceof UserStatus) {
            $profileData['status'] = $profileData['status']->value;
        }
        if (!array_key_exists('status', $profileData) || $profileData['status'] === null || $profileData['status'] === '') {
            $profileData['status'] = config('identity.user.default_status', 'active');
        }

        return $profileData;
    }
}
