<?php

namespace App\Events;

use App\Models\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class PlayerEliminated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Player $player,
        public ?string $cause = null,
        public ?string $causeLocale = null
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('room.' . $this->player->room_id);
    }

    public function broadcastWith(): array
    {
        $role = $this->player->role;
        $locale = $this->causeLocale ?? app()->getLocale();

        return [
            'nickname' => $this->player->nickname,
            'role_key' => $role ? $role->key : null,
            'role_name' => $role ? __('roles.' . $role->key . '.name', [], $locale) : null,
            'cause' => $this->cause ? __('game.' . $this->cause, [], $locale) : null,
            'cause_key' => $this->cause,
        ];
    }
}
