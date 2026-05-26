<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Models\User;

class UserSuspended
{
    use Dispatchable, SerializesModels;

    public User $user;
    public ?string $reason;

    public function __construct(User $user, ?string $reason = null)
    {
        $this->user = $user;
        $this->reason = $reason;
    }
}