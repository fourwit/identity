<?php

namespace Modules\Identity\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

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

        return [
            'name'       => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => $requireEmail 
                ? "required|email|unique:users,email,{$userId}" 
                : "nullable|email|unique:users,email,{$userId}",
            'phone'      => $requirePhone 
                ? "required|string|unique:users,phone,{$userId}" 
                : "nullable|string|unique:users,phone,{$userId}",
            'username'   => $requireUsername 
                ? "required|string|unique:users,username,{$userId}" 
                : "nullable|string|unique:users,username,{$userId}",
            'timezone'   => 'nullable|string|timezone',
            'locale'     => 'nullable|string|max:10',
        ];
    }
}
