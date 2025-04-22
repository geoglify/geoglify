<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipsLatestPositionsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $ships
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('ships.latest_positions');
    }

    public function broadcastWith(): array
    {
        return $this->ships;
    }
}
