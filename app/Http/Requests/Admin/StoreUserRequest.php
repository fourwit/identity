<?php

namespace Modules\Identity\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Http\Requests\Concerns\HasUserValidationRules;

use Modules\Identity\Support\IdentityConfig;

class StoreUserRequest extends FormRequest
{
    use HasUserValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', IdentityConfig::userModelClass()) ?? false;
    }

    public function rules(): array
    {
        return $this->getUserRules(false, null, false, true);
    }
}