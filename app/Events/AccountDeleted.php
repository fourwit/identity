<?php

namespace Modules\Identity\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int|string $userId,
        public ?string $name = null,
        public ?string $email = null,
        public string $source = 'web'
    ) {}
}
