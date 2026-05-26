<?php

namespace Modules\Identity\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid) && config('identity.features.uuid', false)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Find a model by UUID
     */
    public static function findByUuid(string $uuid)
    {
        return static::where('uuid', $uuid)->first();
    }
}