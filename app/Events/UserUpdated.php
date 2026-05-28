<?php

namespace Modules\Identity\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

class UserUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
   
    public Model $user;
    public array $changes;

    /**
     * Create a new event instance.
     */
    public function __construct(Model $user, array $changes = [])
    {
        $this->user = $user;
        $this->changes = $changes;
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
