<?php

namespace Modules\Identity\Traits;

use Modules\Identity\Enums\UserStatus;

trait HasStatus
{
    /**
     * Boot the trait
     */
    protected static function bootHasStatus(): void
    {
        static::creating(function ($model) {
            if (!isset($model->status) || $model->status === null || $model->status === '') {
                $model->status = config('identity.user.default_status', 'active');
            }
        });
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === UserStatus::SUSPENDED;
    }

    /**
     * Suspend the user (Observer will dispatch event)
     */
    public function suspend(?string $reason = null): bool
    {
        $this->status = UserStatus::SUSPENDED;
        return $this->save();
    }

    /**
     * Activate the user (Observer will dispatch event)
     */
    public function activate(): bool
    {
        $this->status = UserStatus::ACTIVE;
        return $this->save();
    }
}