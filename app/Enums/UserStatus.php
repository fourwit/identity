<?php

namespace Modules\Identity\Enums;

use Illuminate\Validation\Rules\Rule;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';

    /**
     * Get all status values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::PENDING => 'Pending',
        };
    }

    /**
     * Check if status is active
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Get all statuses for validation rules
     */
    public static function forValidation(): string
    {
        return 'in:' . implode(',', self::values());
    }

    public static function rule(): Rule
    {
        return Rule::enum(self::class);
    }

    public function badgeColor(): string
    {
        return match($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
            self::SUSPENDED => 'danger',
            self::PENDING => 'warning',
        };
    }
}