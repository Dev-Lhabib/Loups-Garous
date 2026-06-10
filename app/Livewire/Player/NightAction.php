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
    public bool $isDecoy = false;
    public ?string $currentActionType = null;
    public bool $actionSelected = false;
    public array $submittedActions = [];
    public bool $wantsMoreActions = false;
    public ?array $decoyPuzzle = null;

    private array $multiActionRoles = ['witch'];
    private array $decoyTypes = ['math', 'riddle', 'count', 'unscramble', 'sequence'];

    public function mount(Room $room, Player $player)
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || $requestPlayer->id !== $player->id) {
            abort(403);
        }

        $this->room = $room;
        $this->player = $player;

        if (!$player->is_alive || $player->is_narrator) return;

        $state = $room->gameState;
        if (!$state || $state->phase !== 'night') return;

        $role = $player->role;

        $this->alivePlayers = Player::where('room_id', $room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->where('id', '!=', $player->id)
            ->orderBy('nickname')
            ->get()
            ->toArray();

        $this->isDecoy = !$role || $role->night_order === null;

        if ($this->isDecoy) {
            $this->generateDecoyPuzzle();
            return;
        }

        $existing = NightActionModel::where('game_state_id', $state->id)
            ->where('player_id', $player->id)
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

    private function generateDecoyPuzzle(): void
    {
        $locale = app()->getLocale();
        $decoys = trans("decoys", [], $locale);
        if (!is_array($decoys)) $decoys = trans("decoys", [], 'en');

        $type = $this->decoyTypes[array_rand($this->decoyTypes)];
        $items = $decoys[$type] ?? [];
        if (empty($items)) {
            $this->decoyPuzzle = ['type' => 'math', 'content' => '13 × 7 = ?'];
            return;
        }
        $this->decoyPuzzle = [
            'type' => $type,
            'content' => $items[array_rand($items)],
        ];
    }

    public function refreshDecoy(): void
    {
        $this->generateDecoyPuzzle();
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
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) {
            abort(403);
        }

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'night' || !$this->player->is_alive) return;

        if ($this->isDecoy) {
            $this->submitted = true;
            $this->confirming = false;
            return;
        }

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

    public function render()
    {
        $role = $this->player->role;

        return view('livewire.player.night-action', [
            'role' => $role,
            'isDecoy' => $this->isDecoy,
            'actionTypes' => $role ? $this->getActionTypesForRole($role->key) : [],
            'isMultiAction' => $role && in_array($role->key, $this->multiActionRoles),
        ]);
    }
}
