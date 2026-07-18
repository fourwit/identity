<?php

namespace Modules\Identity\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

use Modules\Identity\Support\IdentityConfig;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && ($this->user()?->can('update', $this->user()) ?? false);
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'phone'      => 'nullable|string|unique:users,phone,' . auth()->id(),
        ];
    }
}