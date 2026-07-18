<?php

namespace Modules\Identity\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && ($this->user()?->can('update', $this->user()) ?? false);
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string|current_password',
            'password'         => 'required|string|min:8|confirmed',
        ];
    }
}
