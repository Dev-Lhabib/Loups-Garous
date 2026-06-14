<?php

namespace App\Events;

use App\Models\GameState;
use App\Models\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class HunterActionPending implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public GameState $state,
        public Player $hunter,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('room.' . $this->state->room_id);
    }

    public function broadcastWith(): array
    {
        return [
            'hunter_id' => $this->hunter->id,
            'hunter_nickname' => $this->hunter->nickname,
        ];
    }
}
