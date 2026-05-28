<?php

namespace Modules\Identity\Actions;

use Modules\Identity\DTOs\UserData;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Events\UserUpdated;
use Modules\Identity\Services\ActivityLogger;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Exceptions\UserNotFoundException;
use Illuminate\Database\Eloquent\Model;


class UpdateUserAction
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function execute(Model $user, UserData $data, string $source = 'web'): Model
    {
        $oldData = $user->only(['name', 'email', 'status']);

        if ($data->status === null) {
            $data->status = $user->status instanceof UserStatus ? $user->status->value : $user->status;
        }

        // Build update data array
        $updateData = [
            'name'       => $data->name,
            'first_name' => $data->firstName,
            'last_name'  => $data->lastName,
            'email'      => $data->email,
            'phone'      => $data->phone,
            'username'   => $data->username,
            'status'     => UserStatus::tryFrom($data->status),
            'password'   => $data->password,
        ];

        // Remove null values (keep existing values)
        $updateData = array_filter($updateData, fn($value) => $value !== null);

        if (!empty($updateData)) {
            $this->repository->update($user, $updateData);
        }

        // Log activity
        ActivityLogger::log(
            "Updated user: {$user->name}",
            $user,
            ['old' => $oldData, 'new' => $user->only(['name', 'email', 'status'])],
            'updated',
            $source
        );

        event(new UserUpdated($user, $user->getChanges()));

        return $user;
    }
}
