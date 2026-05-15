<?php

namespace Modules\Identity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Modules\Identity\Contracts\HasAvatar;
use Modules\Identity\Database\Factories\UserFactory;

/**
 * This model implements HasAvatar contract.
 * The actual implementation (upload, crop, resize, etc.) 
 * will be handled by the future Media Module.
 */

class User extends Authenticatable implements HasAvatar
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid', 'name', 'first_name', 'last_name', 'email', 'phone',
        'username', 'password', 'avatar_id', 'status', 'timezone',
        'locale', 'two_factor_enabled', 'two_factor_secret', 'metadata',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'metadata' => 'array',
    ];


    // Relationships
    // public function avatar()
    // {
    //     return $this->belongsTo(\Modules\Media\App\Models\Media::class, 'avatar_id');
    // }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (config('identity.enable_uuid') && empty($user->uuid)) {
                $user->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
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
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper Methods
    public function isAdmin(): bool
    {
        // Will be enhanced when RBAC module is added
        return $this->hasRole('admin') ?? false;
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

    public function getAvatarUrl(): ?string
    {
        return $this->avatar; // or return asset('storage/' . $this->avatar);
    }

    public function setAvatar(string $path): void
    {
        $this->avatar = $path;
        $this->save();
    }

    public function removeAvatar(): void
    {
        $this->avatar = null;
        $this->save();
    }
}
