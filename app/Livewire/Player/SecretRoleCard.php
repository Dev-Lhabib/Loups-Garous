<?php

namespace App\Livewire\Player;

use App\Models\CoupleBond;
use App\Models\Player;
use Livewire\Component;

class SecretRoleCard extends Component
{
    public Player $player;
    public bool $revealed = false;
    public ?array $roleData = null;
    public array $teammatesData = [];

    public function mount()
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) {
            return;
        }
    }

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
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
                $this->teammatesData = $this->loadTeammates($role, $this->player);
            }
        }
        $this->revealed = true;
    }

    public function hide(): void
    {
        $this->revealed = false;
        $this->roleData = null;
        $this->teammatesData = [];
    }

    private function loadTeammates($role, Player $player): array
    {
        $roomId = $player->room_id;
        $teammates = [];

        if ($role->key === 'werewolf' || $role->key === 'big_bad_wolf' || $role->key === 'accursed_wolf_father' || $role->key === 'white_werewolf') {
            $wolfKeys = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'wolf_hound'];
            $others = Player::where('room_id', $roomId)
                ->where('id', '!=', $player->id)
                ->where('is_alive', true)
                ->where('is_narrator', false)
                ->whereHas('role', fn ($q) => $q->whereIn('key', $wolfKeys))
                ->with('role')
                ->get();

            foreach ($others as $other) {
                $teammates[] = [
                    'nickname' => $other->nickname,
                    'role_key' => $other->role?->key,
                    'role_name' => $other->role ? __("roles.{$other->role->key}.name") : null,
                ];
            }
        }

        if ($role->key === 'two_sisters') {
            $others = Player::where('room_id', $roomId)
                ->where('id', '!=', $player->id)
                ->where('is_alive', true)
                ->where('is_narrator', false)
                ->whereHas('role', fn ($q) => $q->where('key', 'two_sisters'))
                ->get();

            foreach ($others as $other) {
                $teammates[] = [
                    'nickname' => $other->nickname,
                    'role_key' => 'two_sisters',
                    'role_name' => __("roles.two_sisters.name"),
                ];
            }
        }

        if ($role->key === 'three_brothers') {
            $others = Player::where('room_id', $roomId)
                ->where('id', '!=', $player->id)
                ->where('is_alive', true)
                ->where('is_narrator', false)
                ->whereHas('role', fn ($q) => $q->where('key', 'three_brothers'))
                ->get();

            foreach ($others as $other) {
                $teammates[] = [
                    'nickname' => $other->nickname,
                    'role_key' => 'three_brothers',
                    'role_name' => __("roles.three_brothers.name"),
                ];
            }
        }

        if ($role->key === 'wolf_hound') {
            $state = $player->room?->gameState;
            $data = $state?->data ?? [];
            $chosenSide = $data['wolf_hound_choice'] ?? null;

            if ($chosenSide === 'werewolves') {
                $wolfKeys = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf'];
                $others = Player::where('room_id', $roomId)
                    ->where('id', '!=', $player->id)
                    ->where('is_alive', true)
                    ->where('is_narrator', false)
                    ->whereHas('role', fn ($q) => $q->whereIn('key', $wolfKeys))
                    ->with('role')
                    ->get();

                foreach ($others as $other) {
                    $teammates[] = [
                        'nickname' => $other->nickname,
                        'role_key' => $other->role?->key,
                        'role_name' => $other->role ? __("roles.{$other->role->key}.name") : null,
                    ];
                }
            }
        }

        return $teammates;
    }

    public function render()
    {
        return view('livewire.player.secret-role-card', [
            'teammatesData' => $this->teammatesData,
        ]);
    }
}
