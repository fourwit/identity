<?php

namespace Modules\Identity\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : intval($user);
        return [
            'name'       => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => config('identity.require_email') 
            ? "required|email|unique:users,email,{$userId}" 
            : "nullable|email|unique:users,email,{$userId}",
            'phone'      => config('identity.require_phone') 
            ? "required|string|unique:users,phone,{$userId}" 
            : "nullable|string|unique:users,phone,{$userId}",
            'username'   => config('identity.require_username') 
            ? "required|string|unique:users,username,{$userId}" 
            : "nullable|string|unique:users,username,{$userId}",
            'status'     => 'required|in:active,inactive,suspended,pending',
        ];
    }
}