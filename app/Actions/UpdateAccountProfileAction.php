<?php

namespace Modules\Identity\Actions;

use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Contracts\UserRepositoryInterface;
use Modules\Identity\Events\ProfileUpdated;
use Modules\Identity\Events\UserUpdated;

class UpdateAccountProfileAction
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function execute(Model $user, array $data, string $source = 'web'): Model
    {
        $allowed = ['name', 'email', 'email_verified_at', 'first_name', 'last_name', 'phone', 'username', 'timezone', 'locale'];
        $payload = array_intersect_key($data, array_flip($allowed));

        if (array_key_exists('email', $payload) && (string) $payload['email'] !== (string) $user->email) {
            $payload['email_verified_at'] = null;
        }

        $this->repository->update($user, $payload);

        $fresh = $this->repository->findByIdOrFail((int) $user->getKey());

        event(new ProfileUpdated($fresh, $payload, $source));
        event(new UserUpdated($fresh, $payload));

        return $fresh;
    }
}
