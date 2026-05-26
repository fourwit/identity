<?php

namespace Modules\Identity\Http\Requests\Concerns;

use Modules\Identity\Enums\UserStatus;

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

        $emailRule = $isUpdate 
            ? ($requireEmail ? "{$prefix}required|email|unique:users,email,{$userId}" : "nullable|email|unique:users,email,{$userId}")
            : ($requireEmail ? 'required|email|unique:users,email' : 'nullable|email|unique:users,email');

        $phoneRule = $isUpdate 
            ? ($requirePhone ? "{$prefix}required|string|unique:users,phone,{$userId}" : "nullable|string|unique:users,phone,{$userId}")
            : ($requirePhone ? 'required|string|unique:users,phone' : 'nullable|string|unique:users,phone');

        $usernameRule = $isUpdate 
            ? ($requireUsername ? "{$prefix}required|string|unique:users,username,{$userId}" : "nullable|string|unique:users,username,{$userId}")
            : ($requireUsername ? 'required|string|unique:users,username' : 'nullable|string|unique:users,username');

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
