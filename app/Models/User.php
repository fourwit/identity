<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Modules\Identity\Database\Factories\UserFactory;

/**
 * This model implements HasAvatar contract.
 * The actual implementation (upload, crop, resize, etc.) 
 * will be handled by the future Media Module.
 */

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value && !str_starts_with($value, '$2y$') 
                ? bcrypt($value) 
                : $value,
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? strtolower($value) : $value,
        );
    }

    public function identityProfile()
    {
        return $this->hasOne(IdentityProfile::class);
    }

    public function getFullNameAttribute(): string
    {
        $first = (string) ($this->first_name ?? '');
        $last = (string) ($this->last_name ?? '');
        $full = trim("{$first} {$last}");
        return $full !== '' ? $full : (string) $this->name;
    }
}
