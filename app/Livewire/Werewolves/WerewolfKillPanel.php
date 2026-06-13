<?php

namespace App\Livewire\Werewolves;

use App\Events\NightActionSubmitted;
use App\Models\GameState;
use App\Models\NightAction;
use App\Models\Player;
use App\Models\Room;
use Livewire\Component;

class WerewolfKillPanel extends Component
{
    public Room $room;
    public Player $player;
    public ?string $selectedTargetId = null;
    public bool $confirming = false;
    public array $aliveTargets = [];
    public array $wolfSelections = [];
    public bool $submitted = false;
    public bool $allAgree = false;
    public ?string $agreedTargetId = null;

    public function mount(Room $room, Player $player)
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || $requestPlayer->id !== $player->id) abort(403);

        $this->room = $room;
        $this->player = $player->fresh(['role']);

        $this->refreshPanel();
    }

    public function refreshPanel(): void
    {
        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $role = $this->player->role;
        if (!$role || !in_array($role->key, ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf'])) {
            return;
        }

        $werewolfRoleKeys = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'wolf_hound'];

        $this->aliveTargets = Player::where('room_id', $this->room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->whereDoesntHave('role', fn ($q) => $q->whereIn('key', $werewolfRoleKeys))
            ->orderBy('nickname')
            ->get()
            ->map(fn ($p) => ['id' => $p->id, 'nickname' => $p->nickname])
            ->toArray();

        $data = $state->data ?? [];
        $this->wolfSelections = $data['werewolf_kill_selections'] ?? [];

        $existing = NightAction::where('game_state_id', $state->id)
            ->where('player_id', $this->player->id)
            ->where('action_type', 'kill')
            ->whereNull('resolved_at')
            ->exists();

        $this->submitted = $existing;

        $this->checkAgreement();
    }

    public function selectTarget(string $targetId): void
    {
        if ($this->submitted) return;

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $data = $state->data ?? [];
        $data['werewolf_kill_selections'][$this->player->id] = $targetId;
        $state->data = $data;
        $state->save();

        $this->selectedTargetId = $targetId;
        $this->wolfSelections = $data['werewolf_kill_selections'];
        $this->checkAgreement();
    }

    public function confirmKill(): void
    {
        if ($this->submitted || !$this->allAgree || !$this->agreedTargetId) return;

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $action = app(\App\Game\Services\ActionService::class)->submit($this->player, [
            'action_type' => 'kill',
            'target_id' => $this->agreedTargetId,
        ]);

        if ($action) {
            $this->submitted = true;
            $this->confirming = false;

            $data = $state->data ?? [];
            unset($data['werewolf_kill_selections'][$this->player->id]);
            $state->data = $data;
            $state->save();
        }
    }

    private function checkAgreement(): void
    {
        $selections = array_values($this->wolfSelections);
        $selections = array_filter($selections);

        if (count($selections) < 1) {
            $this->allAgree = false;
            $this->agreedTargetId = null;
            return;
        }

        $unique = array_unique($selections);
        $this->allAgree = count($unique) === 1;
        $this->agreedTargetId = $this->allAgree ? $unique[0] : null;
    }

    public function getWolfName(int $wolfId): string
    {
        $wolf = Player::find($wolfId);
        return $wolf?->nickname ?? 'Unknown';
    }

    public function getListeners()
    {
        return [
            "echo-private:room.{$this->room->id},PhaseChanged" => '$refresh',
        ];
    }

    public function render()
    {
        $this->refreshPanel();

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night' || $this->submitted) {
            return '<div></div>';
        }

        return view('livewire.werewolves.werewolf-kill-panel');
    }
}
