<?php

namespace Modules\Identity\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Http\Requests\Concerns\HasUserValidationRules;

class StoreUserRequest extends FormRequest
{
    use HasUserValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->getUserRules(false, null, false, true);
    }
}