<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Identity\Enums\UserStatus;

class IdentityProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'uuid',
        'first_name',
        'last_name',
        'username',
        'phone',
        'avatar_id',
        'status',
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

    protected $casts = [
        'status' => UserStatus::class,
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'remember_me' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    public function getTable()
    {
        return config('identity.tables.profiles', parent::getTable());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
