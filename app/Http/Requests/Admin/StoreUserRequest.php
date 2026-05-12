<?php

namespace Modules\Identity\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => config('identity.require_email') ? 'required|email|unique:users,email' : 'nullable|email|unique:users,email',
            'phone'      => config('identity.require_phone') ? 'required|string|unique:users,phone' : 'nullable|string|unique:users,phone',
            'username'   => config('identity.require_username') ? 'required|string|unique:users,username' : 'nullable|string|unique:users,username',
            'status'     => 'required|in:active,inactive,suspended,pending',
        ];
    }
}