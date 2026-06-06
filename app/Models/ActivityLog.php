<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Identity\Database\Factories\ActivityLogFactory;

class ActivityLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'source',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer()
    {
        return $this->morphTo();
    }

    protected static function newFactory()
    {
        return ActivityLogFactory::new();
    }
}
