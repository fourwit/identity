<?php

namespace Modules\Identity\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors()
        ], 422));
    }
}