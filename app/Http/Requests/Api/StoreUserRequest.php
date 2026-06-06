<?php

namespace Modules\Identity\Http\Requests\Api;

use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Http\Requests\Concerns\HasUserValidationRules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    use HasUserValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = $this->getUserRules(false, null, true, false);
        $rules['password'] = 'required|string|min:8';

        return $rules;
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
