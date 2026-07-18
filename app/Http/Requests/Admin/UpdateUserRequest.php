<?php

namespace Modules\Identity\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Http\Requests\Concerns\HasUserValidationRules;
use Modules\Identity\Support\IdentityConfig;
use Illuminate\Database\Eloquent\Model;

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
        return $this->getUserRules(true, $userId, false, true);
    }
}