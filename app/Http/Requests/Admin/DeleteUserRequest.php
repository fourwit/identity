<?php

namespace Modules\Identity\Http\Requests\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Identity\Support\IdentityConfig;

class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');
        $model = $user instanceof Model
            ? $user
            : IdentityConfig::userModelClass()::query()->findOrFail((int) $user);

        return $this->user()?->can('delete', $model) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
