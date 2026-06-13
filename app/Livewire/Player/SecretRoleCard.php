<?php

namespace App\Livewire\Player;

use App\Models\Player;
use Livewire\Component;

class SecretRoleCard extends Component
{
    public Player $player;
    public bool $revealed = false;
    public ?array $roleData = null;

    public function mount()
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) {
            abort(403);
        }
    }

    public function reveal(): void
    {
        if (!$this->roleData) {
            $role = $this->player->role;
            if ($role) {
                $this->roleData = [
                    'key' => $role->key,
                    'name' => __("roles.{$role->key}.name"),
                    'description' => __("roles.{$role->key}.description"),
                    'faction' => $role->faction,
                    'night_order' => $role->night_order,
                    'has_night_action' => $role->night_order !== null,
                ];
            }
        }
        $this->revealed = true;
    }

    public function hide(): void
    {
        $this->revealed = false;
        $this->roleData = null;
    }

    public function render()
    {
        return view('livewire.player.secret-role-card');
    }
}
