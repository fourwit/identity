<?php

namespace Modules\Identity\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Identity\Http\Requests\Concerns\HasUserValidationRules;
use Modules\Identity\Support\IdentityConfig;

class UpdateUserRequest extends FormRequest
{
    use HasUserValidationRules;

    public function authorize(): bool
    {
        $user = $this->route('user');
        $model = $user instanceof Model
            ? $user
            : IdentityConfig::userModelClass()::query()->findOrFail((int) $user);

        return $this->user()?->can('update', $model) ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : intval($user);

        return $this->getUserRules(true, $userId, true, false);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
