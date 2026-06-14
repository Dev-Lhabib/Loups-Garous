<?php

namespace App\Livewire\Player;

use App\Models\Player;
use Livewire\Component;

class RoleCard extends Component
{
    public Player $player;
    public bool $revealed = false;

    public function mount()
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) {
            abort(403);
        }
    }

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    }

    public function reveal()
    {
        $this->revealed = true;
    }

    public function hide()
    {
        $this->revealed = false;
    }

    public function render()
    {
        return view('livewire.player.role-card', [
            'role' => $this->player->role,
        ]);
    }
}
