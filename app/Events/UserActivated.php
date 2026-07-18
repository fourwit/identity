<?php

namespace Modules\Identity\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Support\UserEventData;

class UserActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $id,
        public readonly ?string $uuid,
        public readonly string $email,
        public readonly ?string $name,
        public readonly string $status,
    ) {}

    public static function fromModel(Model $user): self
    {
        return new self(
            id: UserEventData::id($user),
            uuid: UserEventData::uuid($user),
            email: (string) $user->email,
            name: $user->name !== null ? (string) $user->name : null,
            status: UserEventData::statusValue($user),
        );
    }
}
