<?php

namespace Modules\Identity\Http\Requests\Concerns;

use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Support\IdentityConfig;

trait HasUserValidationRules
{
    /**
     * Get the validation rules for user store/update.
     *
     * @param bool $isUpdate
     * @param mixed $userId
     * @param bool $isApi
     * @param bool $confirmPassword
     * @return array
     */
    protected function getUserRules(bool $isUpdate = false, $userId = null, bool $isApi = false, bool $confirmPassword = false): array
    {
        $prefix = ($isUpdate && $isApi) ? 'sometimes|' : '';
        $requireEmail = config('identity.user.require_email');
        $requirePhone = config('identity.user.require_phone');
        $requireUsername = config('identity.user.require_username');
        $usersTable = IdentityConfig::usersTable();
        $profilesTable = config('identity.tables.profiles', 'identity_profiles');
        $phoneUniqueTable = $profilesTable;
        $usernameUniqueTable = $profilesTable;
        $phoneExceptColumn = 'user_id';
        $usernameExceptColumn = 'user_id';

        $emailRule = $isUpdate 
            ? ($requireEmail ? "{$prefix}required|email|unique:{$usersTable},email,{$userId}" : "nullable|email|unique:{$usersTable},email,{$userId}")
            : ($requireEmail ? "required|email|unique:{$usersTable},email" : "nullable|email|unique:{$usersTable},email");

        $phoneRule = $isUpdate 
            ? ($requirePhone ? "{$prefix}required|string|unique:{$phoneUniqueTable},phone,{$userId},{$phoneExceptColumn}" : "nullable|string|unique:{$phoneUniqueTable},phone,{$userId},{$phoneExceptColumn}")
            : ($requirePhone ? "required|string|unique:{$phoneUniqueTable},phone" : "nullable|string|unique:{$phoneUniqueTable},phone");

        $usernameRule = $isUpdate 
            ? ($requireUsername ? "{$prefix}required|string|unique:{$usernameUniqueTable},username,{$userId},{$usernameExceptColumn}" : "nullable|string|unique:{$usernameUniqueTable},username,{$userId},{$usernameExceptColumn}")
            : ($requireUsername ? "required|string|unique:{$usernameUniqueTable},username" : "nullable|string|unique:{$usernameUniqueTable},username");

        $passwordRule = $isUpdate 
            ? 'sometimes|nullable|string|min:8' 
            : 'nullable|string|min:8';

        if ($confirmPassword) {
            $passwordRule .= '|confirmed';
        }

        return [
            'name'       => $prefix . 'required|string|max:255',
            'first_name' => $prefix . 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => $emailRule,
            'phone'      => $phoneRule,
            'username'   => $usernameRule,
            'status'     => ['required', UserStatus::forValidation()],
            'password'   => $passwordRule,
        ];
    }
}
