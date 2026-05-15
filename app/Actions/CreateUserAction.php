<?php

namespace Modules\Identity\Actions;

use Modules\Identity\DTOs\UserData;
use Modules\Identity\Models\User;
use Modules\Identity\Events\UserCreated;
use Modules\Identity\Services\ActivityLogger;

class CreateUserAction
{
    public function execute(UserData $data, string $source = 'web'): User
    {
        $user = User::create([
            'name'       => $data->name,
            'first_name' => $data->firstName,
            'last_name'  => $data->lastName,
            'email'      => $data->email,
            'phone'      => $data->phone,
            'username'   => $data->username,
            'status'     => $data->status,
        ]);

        // Log activity
        ActivityLogger::log(
            "Created new user: {$user->name}",
            $user,
            ['email' => $user->email, 'status' => $user->status],
            'created',
            $source
        );

        event(new UserCreated($user));

        return $user;
    }
}