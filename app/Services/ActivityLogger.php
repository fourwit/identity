<?php

namespace Modules\Identity\Services;

use Modules\Identity\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(
        string $description,
        $subject = null,
        array $properties = [],
        string $event = null,
        string $source = 'web'
    ) {
        $causer = Auth::user();
        ActivityLog::create([
            'log_name'     => 'user',
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
            'causer_type'  => $causer ? get_class($causer) : null,
            'causer_id'    => $causer?->id,
            'properties'   => $properties,
            'event'        => $event,
            'source'       => $source,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }
}