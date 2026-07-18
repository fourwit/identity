<?php

namespace Modules\Identity\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(Model $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(Model $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    public function create(Model $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(Model $user, Model $model): bool
    {
        return (int) $user->getKey() === (int) $model->getKey() || $this->isAdmin($user);
    }

    public function delete(Model $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    protected function isAdmin(Model $user): bool
    {
        if (method_exists($user, 'hasRole')) {
            return (bool) $user->hasRole('admin');
        }

        if (method_exists($user, 'isAdmin')) {
            return (bool) $user->isAdmin();
        }

        return false;
    }
}
