<?php

namespace Modules\Identity\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(Model $user): bool
    {
        return $user->hasRole('admin'); // Will work after RBAC module
    }

    public function update(Model $user, Model $model): bool
    {
        return $user->id === $model->id || $user->hasRole('admin');
    }
}
