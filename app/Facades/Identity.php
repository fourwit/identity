<?php

namespace Modules\Identity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Modules\Identity\Models\User|null findById(int $id)
 * @method static \Modules\Identity\Models\User|null findByEmail(string $email)
 * @method static \Modules\Identity\Models\User|null findByUuid(string $uuid)
 * @method static mixed getAll(?int $perPage = null)
 * @method static mixed search(?string $term, ?string $status = null, ?int $perPage = null)
 * @method static mixed getActiveUsers(?int $perPage = null)
 * @method static \Modules\Identity\Models\User createUser(array $data)
 * @method static bool updateUser(\Modules\Identity\Models\User $user, array $data)
 * @method static bool deleteUser(\Modules\Identity\Models\User $user)
 */
class Identity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'identity';
    }
}