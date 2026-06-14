<?php

namespace App\Livewire\Player;

use App\Models\NightAction as NightActionModel;
use App\Models\Player;
use App\Models\Room;
use Livewire\Component;

class NightAction extends Component
{
    public Room $room;
    public Player $player;
    public ?string $selectedTargetId = null;
    public bool $submitted = false;
    public bool $confirming = false;
    public ?NightActionModel $submittedAction = null;
    public array $alivePlayers = [];
    public bool $hasNightAction = false;
    public ?string $currentActionType = null;
    public bool $actionSelected = false;
    public array $submittedActions = [];
    public bool $wantsMoreActions = false;
    public ?string $wolfHoundSide = null;
    public array $decoy = [];
    public bool $panelRevealed = false;
    public ?array $roleData = null;

    private array $multiActionRoles = ['witch'];

    public function mount(Room $room, Player $player)
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || $requestPlayer->id !== $player->id) {
            abort(403);
        }

        $this->room = $room;
        $this->player = $player->fresh(['role']);

        if (!$this->player->is_alive || $this->player->is_narrator) return;

        $state = $room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $role = $this->player->role;

        $this->hasNightAction = $role && $role->night_order !== null;

        if (!$this->hasNightAction) {
            $this->decoy = \App\Helpers\DecoyHelper::random(app()->getLocale());
            return;
        }

        $this->alivePlayers = Player::where('room_id', $room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->where('id', '!=', $this->player->id)
            ->orderBy('nickname')
            ->get()
            ->toArray();

        $existing = NightActionModel::where('game_state_id', $state->id)
            ->where('player_id', $this->player->id)
            ->whereNull('resolved_at')
            ->get();

        if ($existing->isNotEmpty()) {
            $this->submitted = true;
            $this->submittedAction = $existing->first();
            $this->submittedActions = $existing->pluck('action_type')->toArray();

            $remainingTypes = array_diff($this->getActionTypesForRole($role->key), $this->submittedActions);
            $this->wantsMoreActions = !empty($remainingTypes);
            return;
        }

        $actionTypes = $this->getActionTypesForRole($role->key);

        if (count($actionTypes) === 1) {
            $this->currentActionType = $actionTypes[0];
            $this->actionSelected = true;
        }
    }

    public function selectActionType(string $actionType): void
    {
        $this->currentActionType = $actionType;
        $this->actionSelected = true;
    }

    public function selectTarget(string $targetId)
    {
        $this->selectedTargetId = $targetId;
        $this->confirming = true;
    }

    public function cancelSelection()
    {
        $this->selectedTargetId = null;
        $this->confirming = false;
    }

    public function submitAnother(): void
    {
        $this->submitted = false;
        $this->submittedAction = null;
        $this->confirming = false;
        $this->selectedTargetId = null;
        $this->actionSelected = false;
        $this->currentActionType = null;
    }

    public function selectWolfHoundSide(string $side): void
    {
        $this->wolfHoundSide = $side;
        $this->confirming = true;
    }

    public function confirmWolfHoundSide(): void
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night' || !$this->player->is_alive) return;

        $action = app(\App\Game\Services\ActionService::class)->submit($this->player, [
            'action_type' => 'choose_side',
            'metadata' => ['side' => $this->wolfHoundSide],
        ]);

        if ($action) {
            $this->submittedActions[] = 'choose_side';
            $this->submitted = true;
            $this->submittedAction = $action;
            $this->confirming = false;
            $this->wolfHoundSide = null;
            $this->wantsMoreActions = false;
        }
    }

    public function refreshDecoy(): void
    {
        $this->decoy = \App\Helpers\DecoyHelper::random(app()->getLocale());
    }

    public function revealPanel(): void
    {
        if (!$this->roleData) {
            $role = $this->player->role;
            if ($role) {
                $this->roleData = [
                    'key' => $role->key,
                    'name' => __("roles.{$role->key}.name"),
                    'has_night_action' => $role->night_order !== null,
                    'action_prompt' => __("ui.roles.{$role->key}.action_prompt"),
                ];
            }
        }
        $this->panelRevealed = true;
    }

    public function hidePanel(): void
    {
        $this->panelRevealed = false;
    }

    private function getActionTypesForRole(string $roleKey): array
    {
        return match ($roleKey) {
            'werewolf' => ['kill'],
            'big_bad_wolf' => ['extra_kill'],
            'accursed_wolf_father' => ['convert'],
            'white_werewolf' => ['solo_kill'],
            'bodyguard' => ['protect'],
            'seer' => ['inspect'],
            'witch' => ['save', 'poison'],
            'pied_piper' => ['enchant'],
            'fox' => ['sniff'],
            'cupid' => ['link_lovers'],
            'wolf_hound' => ['choose_side'],
            default => [],
        };
    }

    public function confirmSubmit()
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) {
            abort(403);
        }

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night' || !$this->player->is_alive) return;

        if (!$this->hasNightAction) return;

        $role = $this->player->role;
        if (!$role || $role->night_order === null) return;

        $actionType = $this->currentActionType;
        if (!$actionType) return;

        $action = app(\App\Game\Services\ActionService::class)->submit($this->player, [
            'action_type' => $actionType,
            'target_id' => $this->selectedTargetId,
        ]);

        if ($action) {
            $this->submittedActions[] = $actionType;
            $this->submitted = true;
            $this->submittedAction = $action;
            $this->confirming = false;

            $remainingTypes = array_diff($this->getActionTypesForRole($role->key), $this->submittedActions);
            $this->wantsMoreActions = !empty($remainingTypes);
        }
    }

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    }

    public function render()
    {
        if (!$this->player->relationLoaded('role')) {
            $this->player->load('role');
        }

        $role = $this->player->role;

        return view('livewire.player.night-action', [
            'role' => $role,
            'hasNightAction' => $this->hasNightAction,
            'actionTypes' => $role ? $this->getActionTypesForRole($role->key) : [],
            'isMultiAction' => $role && in_array($role->key, $this->multiActionRoles),
            'decoy' => $this->decoy,
            'panelRevealed' => $this->panelRevealed,
            'roleData' => $this->roleData,
        ]);
    }
}
