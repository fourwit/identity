<?php

namespace Modules\Identity\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Support\UserEventData;

class UserUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $id,
        public readonly array $changes,
        public readonly ?string $email = null,
        public readonly ?string $name = null,
    ) {}

    public static function fromModel(Model $user, array $changes = []): self
    {
        return new self(
            id: UserEventData::id($user),
            changes: $changes,
            email: $user->email !== null ? (string) $user->email : null,
            name: $user->name !== null ? (string) $user->name : null,
        );
    }
}
