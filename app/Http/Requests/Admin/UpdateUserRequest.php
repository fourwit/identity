<?php

namespace Modules\Identity\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
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
        return $this->getUserRules(true, $userId, false, true);
    }
}