<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class UserActivated
{
    use Dispatchable, SerializesModels;

    public Model $user;

    public function __construct(Model $user)
    {
        $this->user = $user;
    }
}
