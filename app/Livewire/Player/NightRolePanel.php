<?php

namespace App\Livewire\Player;

use App\Models\GameState;
use App\Models\NightAction as NightActionModel;
use App\Models\Player;
use App\Models\Room;
use Livewire\Component;

class NightRolePanel extends Component
{
    public Room $room;
    public Player $player;
    public bool $panelOpen = false;
    public ?string $selectedTargetId = null;
    public bool $confirming = false;
    public array $alivePlayers = [];
    public bool $actionCompleted = false;

    public bool $isWerewolfFaction = false;
    public array $wolfSelections = [];
    public bool $allAgree = false;
    public ?string $agreedTargetId = null;

    public bool $isWitch = false;
    public bool $witchSaveUsed = false;
    public bool $witchPoisonUsed = false;

    public function mount(Room $room, Player $player): void
    {
        $this->room = $room;
        $this->player = $player->fresh(['role']);

        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $player->id) {
            return;
        }

        if (!$this->player->is_alive || $this->player->is_narrator) return;

        $state = $room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $role = $this->player->role;
        if (!$role) return;

        $wolfKeys = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf'];
        $this->isWerewolfFaction = in_array($role->key, $wolfKeys);
        $this->isWitch = $role->key === 'witch';

        $this->checkActionCompleted($state);

        if ($this->isWerewolfFaction) {
            $werewolfRoleKeys = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'wolf_hound'];
            $this->alivePlayers = Player::where('room_id', $this->room->id)
                ->where('is_alive', true)
                ->where('is_narrator', false)
                ->whereDoesntHave('role', fn ($q) => $q->whereIn('key', $werewolfRoleKeys))
                ->orderBy('nickname')
                ->get()
                ->map(fn ($p) => ['id' => $p->id, 'nickname' => $p->nickname])
                ->toArray();

            $data = $state->data ?? [];
            $this->wolfSelections = $data['werewolf_kill_selections'] ?? [];
            $this->checkWolfAgreement();
        } else {
            $this->alivePlayers = Player::where('room_id', $this->room->id)
                ->where('is_alive', true)
                ->where('is_narrator', false)
                ->where('id', '!=', $this->player->id)
                ->orderBy('nickname')
                ->get()
                ->map(fn ($p) => ['id' => $p->id, 'nickname' => $p->nickname])
                ->toArray();
        }

        if ($this->isWitch) {
            $data = $state->data ?? [];
            $this->witchSaveUsed = !empty($data['witch_save_used']);
            $this->witchPoisonUsed = !empty($data['witch_poison_used']);
        }
    }

    public function openPanel(): void
    {
        if ($this->panelOpen) return;
        $this->panelOpen = true;
    }

    public function closePanel(): void
    {
        $this->panelOpen = false;
        $this->selectedTargetId = null;
        $this->confirming = false;
    }

    public function selectTarget(string $targetId): void
    {
        if ($this->actionCompleted) return;
        $this->selectedTargetId = $targetId;
        $this->confirming = true;
    }

    public function cancelSelection(): void
    {
        $this->selectedTargetId = null;
        $this->confirming = false;
    }

    public function confirmAction(string $actionType = 'default'): void
    {
        if ($this->actionCompleted) return;

        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night' || !$this->player->is_alive) return;

        $role = $this->player->role;
        if (!$role) return;

        $actionTypeToSubmit = $this->resolveActionType($role->key, $actionType);
        if (!$actionTypeToSubmit) {
            $this->closePanel();
            return;
        }

        $result = app(\App\Game\Services\ActionService::class)->submit($this->player, [
            'action_type' => $actionTypeToSubmit,
            'target_id' => $this->selectedTargetId,
        ]);

        if ($result) {
            $this->actionCompleted = true;
        }

        $this->closePanel();
    }

    public function witchUseSave(): void
    {
        if ($this->actionCompleted) return;

        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night' || !$this->player->is_alive) return;
        if ($this->witchSaveUsed || !$this->selectedTargetId) return;

        $result = app(\App\Game\Services\ActionService::class)->submit($this->player, [
            'action_type' => 'save',
            'target_id' => $this->selectedTargetId,
        ]);

        if ($result) {
            $this->witchSaveUsed = true;
            $this->actionCompleted = true;
        }

        $this->closePanel();
    }

    public function witchUsePoison(): void
    {
        if ($this->actionCompleted) return;

        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night' || !$this->player->is_alive) return;
        if ($this->witchPoisonUsed || !$this->selectedTargetId) return;

        $result = app(\App\Game\Services\ActionService::class)->submit($this->player, [
            'action_type' => 'poison',
            'target_id' => $this->selectedTargetId,
        ]);

        if ($result) {
            $this->witchPoisonUsed = true;
            $this->actionCompleted = true;
        }

        $this->closePanel();
    }

    public function wolfSelectTarget(string $targetId): void
    {
        if ($this->actionCompleted) return;

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $data = $state->data ?? [];
        $data['werewolf_kill_selections'][$this->player->id] = $targetId;
        $state->data = $data;
        $state->save();

        $this->selectedTargetId = $targetId;
        $this->confirming = true;
        $this->wolfSelections = $data['werewolf_kill_selections'];
        $this->checkWolfAgreement();
    }

    public function wolfConfirmKill(): void
    {
        if ($this->actionCompleted) return;
        if (!$this->allAgree || !$this->agreedTargetId) return;

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $submitted = NightActionModel::where('game_state_id', $state->id)
            ->where('player_id', $this->player->id)
            ->where('action_type', 'kill')
            ->whereNull('resolved_at')
            ->exists();
        if ($submitted) return;

        $result = app(\App\Game\Services\ActionService::class)->submit($this->player, [
            'action_type' => 'kill',
            'target_id' => $this->agreedTargetId,
        ]);

        if ($result) {
            $this->actionCompleted = true;

            $data = $state->data ?? [];
            unset($data['werewolf_kill_selections'][$this->player->id]);
            $state->data = $data;
            $state->save();
        }

        $this->closePanel();
    }

    private function checkActionCompleted(GameState $state): void
    {
        $existing = NightActionModel::where('game_state_id', $state->id)
            ->where('player_id', $this->player->id)
            ->whereNull('resolved_at')
            ->exists();
        $this->actionCompleted = $existing;

        if ($this->isWitch && $this->witchSaveUsed && $this->witchPoisonUsed) {
            $this->actionCompleted = true;
        }
    }

    private function resolveActionType(string $roleKey, string $uiAction): string
    {
        return match ($roleKey) {
            'werewolf' => 'kill',
            'big_bad_wolf' => 'extra_kill',
            'accursed_wolf_father' => 'convert',
            'white_werewolf' => 'solo_kill',
            'bodyguard' => 'protect',
            'seer' => 'inspect',
            'witch' => $uiAction === 'save' ? 'save' : 'poison',
            'pied_piper' => 'enchant',
            'fox' => 'sniff',
            'cupid' => 'link_lovers',
            'wolf_hound' => 'choose_side',
            default => '',
        };
    }

    private function checkWolfAgreement(): void
    {
        $selections = array_filter(array_values($this->wolfSelections));

        if (count($selections) < 1) {
            $this->allAgree = false;
            $this->agreedTargetId = null;
            return;
        }

        $unique = array_unique($selections);
        $this->allAgree = count($unique) === 1;
        $this->agreedTargetId = $this->allAgree ? $unique[0] : null;
    }

    public function getListeners(): array
    {
        return [
            "echo-private:room.{$this->room->id},PhaseChanged" => '$refresh',
        ];
    }

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    }

    public function render()
    {
        return view('livewire.player.night-role-panel', [
            'alivePlayers' => $this->alivePlayers,
            'wolfSelections' => $this->wolfSelections,
            'allAgree' => $this->allAgree,
            'agreedTargetId' => $this->agreedTargetId,
        ]);
    }
}
