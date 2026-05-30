<?php

namespace Modules\Identity\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public int $userId;
    public string $userName;
    public string $source;

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, string $userName, string $source = 'web')
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->source = $source;
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
