<?php
namespace Modules\Identity\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Http\Requests\Concerns\HasUserValidationRules;

class UpdateUserRequest extends FormRequest
{
    use HasUserValidationRules;

    public function authorize(): bool
    {
        return true;
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
            'errors'  => $validator->errors()
        ], 422));
    }
}