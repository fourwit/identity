<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Modules\Identity\Traits\HasUuid;
use Modules\Identity\Traits\HasStatus;


use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Database\Factories\UserFactory;

/**
 * This model implements HasAvatar contract.
 * The actual implementation (upload, crop, resize, etc.) 
 * will be handled by the future Media Module.
 */

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasUuid, HasStatus;

    protected $fillable = [
        'uuid', 'name', 'first_name', 'last_name', 'email', 'phone',
        'username', 'password', 'avatar_id', 'status', 'timezone',
        'locale', 'two_factor_enabled', 'two_factor_secret', 'metadata',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
    ];

    protected $casts = [
        'status' => UserStatus::class,
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    // Accessors
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . $this->last_name) ?: $this->name,
        );
    }

    // Scopes
    /**
     * Scope: Only active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE);
    }

    /**
     * Scope: Only verified users (email + phone verified)
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at')
                    ->whereNotNull('phone_verified_at');
    }

    /**
     * Scope: Search by name, email, or phone
     */
    public function scopeSearch($query, string $term)
    {
        // Read searchable fields from config (single source of truth)
        $fields = config('identity.user.searchable_fields', ['name', 'email', 'phone']);
        
        return $query->where(function ($q) use ($term, $fields) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', "%{$term}%");
            }
        });
    }

    /**
     * Scope: Only suspended users
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', UserStatus::SUSPENDED);
    }

    /**
     * Scope: Only pending users
     */
    public function scopePending($query)
    {
        return $query->where('status', UserStatus::PENDING);
    }

    public function getMetadata(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->update(['metadata' => $metadata]);
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value && !str_starts_with($value, '$2y$') 
                ? bcrypt($value) 
                : $value,
        );
    }
}
