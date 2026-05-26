<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Models\User;

class UserActivated
{
    use Dispatchable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}