<?php

namespace Modules\Identity\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Support\UserEventData;

class UserSuspended
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $id,
        public readonly ?string $uuid,
        public readonly string $email,
        public readonly ?string $name,
        public readonly string $status,
        public readonly ?string $reason = null,
    ) {}

    public static function fromModel(Model $user, ?string $reason = null): self
    {
        return new self(
            id: UserEventData::id($user),
            uuid: UserEventData::uuid($user),
            email: (string) $user->email,
            name: $user->name !== null ? (string) $user->name : null,
            status: UserEventData::statusValue($user),
            reason: $reason,
        );
    }
}
