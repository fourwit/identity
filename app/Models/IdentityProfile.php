<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IdentityProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'phone',
        'avatar_id',
        'status',
        'timezone',
        'locale',
        'two_factor_enabled',
        'two_factor_secret',
        'metadata',
    ];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    public function getTable()
    {
        return config('identity.tables.profiles', parent::getTable());
    }
}
