<?php

namespace Modules\Identity\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\Support\IdentityConfig;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth()->id();
        $requireEmail = config('identity.user.require_email');
        $requirePhone = config('identity.user.require_phone');
        $requireUsername = config('identity.user.require_username');
        $usersTable = IdentityConfig::usersTable();
        $profilesTable = config('identity.tables.profiles', 'identity_profiles');
        $phoneUniqueTable = IdentityConfig::isOwnedMode() ? $usersTable : $profilesTable;
        $usernameUniqueTable = IdentityConfig::isOwnedMode() ? $usersTable : $profilesTable;
        $phoneExceptColumn = IdentityConfig::isOwnedMode() ? 'id' : 'user_id';
        $usernameExceptColumn = IdentityConfig::isOwnedMode() ? 'id' : 'user_id';

        return [
            'name'       => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => $requireEmail 
                ? "required|email|unique:{$usersTable},email,{$userId}" 
                : "nullable|email|unique:{$usersTable},email,{$userId}",
            'phone'      => $requirePhone 
                ? "required|string|unique:{$phoneUniqueTable},phone,{$userId},{$phoneExceptColumn}" 
                : "nullable|string|unique:{$phoneUniqueTable},phone,{$userId},{$phoneExceptColumn}",
            'username'   => $requireUsername 
                ? "required|string|unique:{$usernameUniqueTable},username,{$userId},{$usernameExceptColumn}" 
                : "nullable|string|unique:{$usernameUniqueTable},username,{$userId},{$usernameExceptColumn}",
            'timezone'   => 'nullable|string|timezone',
            'locale'     => 'nullable|string|max:10',
        ];
    }
}
