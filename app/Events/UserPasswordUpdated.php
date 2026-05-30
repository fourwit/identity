<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPasswordUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public object $user,
        public string $source = 'web'
    ) {}
}
