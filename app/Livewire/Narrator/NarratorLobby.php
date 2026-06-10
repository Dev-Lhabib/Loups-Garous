<?php

namespace App\Livewire\Narrator;

use App\Events\PlayerLeft;
use App\Events\SuspiciousAccessAttempt;
use App\Game\Engine\GameEngine;
use App\Game\Services\LobbyService;
use App\Game\Services\RoleConfigValidator;
use App\Helpers\QrHelper;
use App\Models\Player;
use App\Models\Role;
use App\Models\Room;
use Livewire\Component;

class NarratorLobby extends Component
{
    public Room $room;
    public string $qrSvg = '';
    public array $roleCounts = [];
    public int $playerCount = 0;
    public array $validationErrors = [];
    public array $roleErrors = [];
    public array $warnings = [];
    public string $balanceStatus = 'unbalanced';
    public bool $canStart = false;

    public function mount(Room $room)
    {
        $player = request()->get('_player');

        if (!$player || !$player->is_narrator || $player->room_id !== $room->id) {
            if ($player) {
                event(new SuspiciousAccessAttempt($player, 'Non-narrator attempted to access narrator lobby'));
            }
            $this->redirect(route('home'));
            return;
        }

        $this->room = $room;

        $ngrokUrl = $room->settings['ngrok_url'] ?? env('APP_URL', 'http://localhost');
        $joinUrl = rtrim($ngrokUrl, '/') . '/join/' . $room->code;
        $this->qrSvg = QrHelper::generate($joinUrl);

        $this->refreshPlayerCount();
        $this->initRoleCounts();
        $this->validateConfig();
    }

    public function refreshPlayerCount()
    {
        $this->playerCount = Player::where('room_id', $this->room->id)
            ->where('is_narrator', false)
            ->count();
    }

    public function initRoleCounts()
    {
        $saved = $this->room->settings['role_counts'] ?? [];
        $roles = Role::orderBy('faction')->orderBy('key')->get();

        foreach ($roles as $role) {
            $this->roleCounts[$role->key] = $saved[$role->key] ?? 0;
        }
    }

    public function incrementRole(string $roleKey)
    {
        if (!isset($this->roleCounts[$roleKey])) return;

        $current = $this->roleCounts[$roleKey];
        if (app(RoleConfigValidator::class)->isRoleAtMax($roleKey, $current, $this->playerCount, $this->roleCounts)) return;

        $this->roleCounts[$roleKey]++;
        $this->saveRoleCounts();
    }

    public function decrementRole(string $roleKey)
    {
        if (!isset($this->roleCounts[$roleKey])) return;
        if ($this->roleCounts[$roleKey] <= 0) return;
        $this->roleCounts[$roleKey]--;
        $this->saveRoleCounts();
    }

    private function saveRoleCounts()
    {
        $settings = $this->room->settings ?? [];
        $settings['role_counts'] = $this->roleCounts;
        $this->room->settings = $settings;
        $this->room->save();
        $this->validateConfig();
    }

    public function validateConfig()
    {
        $validator = app(RoleConfigValidator::class);
        $this->validationErrors = $validator->validate($this->playerCount, $this->roleCounts);
        $this->roleErrors = $validator->getPerRoleErrors($this->roleCounts);
        $this->warnings = $validator->getWarnings($this->playerCount, $this->roleCounts);
        $this->balanceStatus = $validator->getBalanceStatus($this->playerCount, $this->roleCounts);
        $this->canStart = empty($this->validationErrors);
    }

    public function loadPreset(int $playerCount)
    {
        $presets = RoleConfigValidator::getPresets();

        if (!isset($presets[$playerCount])) return;

        foreach ($this->roleCounts as $key => $_) {
            $this->roleCounts[$key] = 0;
        }

        foreach ($presets[$playerCount] as $roleKey => $count) {
            if (isset($this->roleCounts[$roleKey])) {
                $this->roleCounts[$roleKey] = $count;
            }
        }

        $this->saveRoleCounts();
    }

    public function removePlayer(int $playerId)
    {
        $player = Player::findOrFail($playerId);
        event(new PlayerLeft($player));
        $player->delete();
        $this->refreshPlayerCount();
        $this->validateConfig();
    }

    public function startGame(GameEngine $engine)
    {
        $player = Player::where('session_token', request()->cookie('session_token'))->first();
        if (!$player || !$player->is_narrator || $player->room_id !== $this->room->id) {
            abort(403);
        }

        $errors = app(LobbyService::class)->validateGameStart($this->room);
        if (!empty($errors)) {
            session()->flash('error', implode(', ', $errors));
            return;
        }

        $engine->startGame($this->room);

        $this->redirect(route('game.narrator', $this->room));
    }

    public function getListeners()
    {
        return [
            "echo-private:room.{$this->room->id},PlayerJoined" => 'refreshPlayerCount',
            "echo-private:room.{$this->room->id},PlayerLeft" => '$refresh',
        ];
    }

    public function render()
    {
        $roles = Role::orderBy('faction')->orderBy('key')->get()->groupBy('faction');

        $this->refreshPlayerCount();
        $this->validateConfig();

        return view('livewire.narrator.narrator-lobby', [
            'roles' => $roles,
            'players' => Player::where('room_id', $this->room->id)
                ->where('is_narrator', false)
                ->orderBy('created_at')
                ->get(),
        ]);
    }
}
