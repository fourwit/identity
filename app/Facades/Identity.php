<?php

namespace Modules\Identity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Modules\Identity\Models\User|null findUserById(int $id)
 * @method static \Modules\Identity\Models\User|null findUserByEmail(string $email)
 * @method static \Modules\Identity\Models\User|null findUserByUuid(string $uuid)
 * @method static string userModel()
 * @method static \Illuminate\Database\Eloquent\Builder userQuery()
 * @method static mixed allUsers(?int $perPage = null)
 * @method static mixed searchUsers(?string $term, ?string $status = null, ?int $perPage = null)
 * @method static mixed activeUsers(?int $perPage = null)
 * @method static \Modules\Identity\Models\User createUser(array $data)
 * @method static bool updateUser(\Modules\Identity\Models\User $user, array $data)
 * @method static bool deleteUser(\Modules\Identity\Models\User $user)
 * @method static int activityLogsCount()
 * @method static \Modules\Identity\Models\User updateAccountProfile(\Modules\Identity\Models\User $user, array $data, string $source = 'web')
 * @method static \Modules\Identity\Models\User updateUserPassword(\Modules\Identity\Models\User $user, string $currentPassword, string $newPassword, string $source = 'web')
 * @method static void deleteOwnAccount(\Modules\Identity\Models\User $user, string $currentPassword, string $source = 'web')
 */
class Identity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'identity';
    }
}
