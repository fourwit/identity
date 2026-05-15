<?php

namespace Modules\Identity\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Identity\Events\UserCreated;
use Modules\Identity\Events\UserUpdated;
use Modules\Identity\Events\UserDeleted;
use Illuminate\Support\Facades\Log;

class LogUserActivity implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        if ($event instanceof UserCreated) {
            Log::info("User Created: {$event->user->name} (ID: {$event->user->id})");
        }

        if ($event instanceof UserUpdated) {
            Log::info("User Updated: {$event->user->name} (ID: {$event->user->id})", $event->changes);
        }

        if ($event instanceof UserDeleted) {
            Log::info("User Deleted: {$event->userName} (ID: {$event->userId})");
        }
    }
}