<?php

namespace App\Events;

use App\Models\Server\Server;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateServerEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, BaseEvent;

    public function __construct(protected Server $server)
    {
        //
    }

    public function getServer(): Server
    {
        return $this->server;
    }
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
