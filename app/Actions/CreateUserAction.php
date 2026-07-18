<?php

namespace Modules\Identity\Actions;

use Modules\Identity\DTOs\UserData;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Events\UserCreated;
use Modules\Identity\Services\ActivityLogger;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Exceptions\UserAlreadyExistsException;

class CreateUserAction
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function execute(UserData $data, string $source = 'web'): Model
    {
        // Check if user already exists
        if ($this->repository->findByEmail($data->email)) {
            throw new UserAlreadyExistsException(
                'A user with this email already exists'
            );
        }

        $user = $this->repository->create([
            'name'       => $data->name,
            'first_name' => $data->firstName,
            'last_name'  => $data->lastName,
            'email'      => $data->email,
            'phone'      => $data->phone,
            'username'   => $data->username,
            'status'     => $data->status,
            'password'   => $data->password,
        ]);

        // Log activity
        ActivityLogger::log(
            "Created new user: {$user->name}",
            $user,
            ['email' => $user->email, 'status' => $user->status],
            'created',
            $source
        );

        event(UserCreated::fromModel($user));

        return $user;
    }
}
