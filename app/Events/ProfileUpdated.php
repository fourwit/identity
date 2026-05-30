<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public object $user,
        public array $changes = [],
        public string $source = 'web'
    ) {}
}
