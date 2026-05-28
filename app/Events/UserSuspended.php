<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class UserSuspended
{
    use Dispatchable, SerializesModels;

    public Model $user;
    public ?string $reason;

    public function __construct(Model $user, ?string $reason = null)
    {
        $this->user = $user;
        $this->reason = $reason;
    }
}
