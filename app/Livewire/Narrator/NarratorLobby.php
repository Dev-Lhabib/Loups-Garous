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
    public int $expectedPlayerCount = 9;
    public string $mode = 'beginner';
    public bool $setupApplied = false;
    public bool $villagerAutoFilled = true;
    public array $validationErrors = [];
    public array $roleErrors = [];
    public array $warnings = [];
    public string $balanceStatus = 'unbalanced';
    public bool $canStart = false;
    public string $joinUrl = '';
    public string $copied = '';
    public bool $showQr = true;
    public bool $firstDayVoting = true;

    public function mount(Room $room)
    {
        $player = $this->resolvePlayerFromSession();

        if (!$player || !$player->is_narrator || $player->room_id !== $room->id) {
            if ($player) {
                event(new SuspiciousAccessAttempt($player, 'Non-narrator attempted to access narrator lobby'));
            }
            $this->redirect(route('home'));
            return;
        }

        $this->room = $room;

        $ngrokUrl = $room->settings['ngrok_url'] ?? env('APP_URL', 'http://localhost');
        $this->joinUrl = rtrim($ngrokUrl, '/') . '/join/' . $room->code;
        $this->qrSvg = QrHelper::generate($this->joinUrl);

        $this->expectedPlayerCount = $room->settings['expected_player_count'] ?? 9;
        $this->mode = $room->settings['config_mode'] ?? 'beginner';
        $this->setupApplied = $room->settings['setup_applied'] ?? false;

        $this->firstDayVoting = $room->settings['first_day_voting'] ?? true;

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

        if ($this->setupApplied || $this->mode === 'beginner') {
            $this->autoFillVillagers();
        }
    }

    public function incrementExpectedPlayers()
    {
        if ($this->expectedPlayerCount >= 24) return;
        $this->expectedPlayerCount++;
        if ($this->mode === 'beginner') {
            $this->setupApplied = false;
        }
        $this->autoFillVillagers();
        $this->saveSettings();
    }

    public function decrementExpectedPlayers()
    {
        if ($this->expectedPlayerCount <= 4) return;
        $this->expectedPlayerCount--;
        if ($this->mode === 'beginner') {
            $this->setupApplied = false;
        }
        $this->autoFillVillagers();
        $this->saveSettings();
    }

    public function toggleMode()
    {
        $this->mode = $this->mode === 'beginner' ? 'advanced' : 'beginner';
        if ($this->mode === 'beginner') {
            $this->autoFillVillagers();
        } elseif ($this->mode === 'advanced') {
            $this->roleCounts['villager'] = 0;
        }
        $this->saveSettings();
    }

    public function applyRecommendedSetup()
    {
        if ($this->expectedPlayerCount < 4) return;

        $setup = app(RoleConfigValidator::class)->getRecommendedSetup($this->expectedPlayerCount);

        foreach ($this->roleCounts as $key => $_) {
            $this->roleCounts[$key] = 0;
        }

        foreach ($setup as $roleKey => $count) {
            if (isset($this->roleCounts[$roleKey])) {
                $this->roleCounts[$roleKey] = $count;
            }
        }

        $this->setupApplied = true;
        $this->villagerAutoFilled = true;
        $this->saveRoleCounts();
    }

    public function incrementRole(string $roleKey)
    {
        if (!isset($this->roleCounts[$roleKey])) return;

        $effectiveCount = $this->expectedPlayerCount > 0 ? $this->expectedPlayerCount : $this->playerCount;
        $current = $this->roleCounts[$roleKey];
        if (app(RoleConfigValidator::class)->isRoleAtMax($roleKey, $current, $effectiveCount, $this->roleCounts)) return;

        $this->setupApplied = false;
        $this->roleCounts[$roleKey]++;

        if ($roleKey !== 'villager') {
            $this->autoFillVillagers();
        }

        $this->villagerAutoFilled = false;
        $this->saveRoleCounts();
    }

    public function decrementRole(string $roleKey)
    {
        if (!isset($this->roleCounts[$roleKey])) return;
        if ($this->roleCounts[$roleKey] <= 0) return;

        $this->setupApplied = false;
        $this->roleCounts[$roleKey]--;

        if ($roleKey !== 'villager') {
            $this->autoFillVillagers();
        }

        $this->villagerAutoFilled = false;
        $this->saveRoleCounts();
    }

    private function autoFillVillagers()
    {
        if ($this->mode === 'advanced') return;
        if ($this->expectedPlayerCount <= 0) return;

        $nonVillagerCount = 0;
        foreach ($this->roleCounts as $key => $count) {
            if ($key !== 'villager') {
                $nonVillagerCount += $count;
            }
        }

        $this->roleCounts['villager'] = max(0, $this->expectedPlayerCount - $nonVillagerCount);
    }

    public function toggleFirstDayVoting()
    {
        $this->firstDayVoting = !$this->firstDayVoting;
        $this->saveGameRules();
    }

    private function saveGameRules()
    {
        $settings = $this->room->settings ?? [];
        $settings['first_day_voting'] = $this->firstDayVoting;
        $this->room->settings = $settings;
        $this->room->save();
    }

    private function saveRoleCounts()
    {
        $settings = $this->room->settings ?? [];
        $settings['role_counts'] = $this->roleCounts;
        $settings['setup_applied'] = $this->setupApplied;
        $settings['expected_player_count'] = $this->expectedPlayerCount;
        $settings['config_mode'] = $this->mode;
        $settings['first_day_voting'] = $this->firstDayVoting;
        $this->room->settings = $settings;
        $this->room->save();
        $this->validateConfig();
    }

    private function saveSettings()
    {
        $settings = $this->room->settings ?? [];
        $settings['expected_player_count'] = $this->expectedPlayerCount;
        $settings['config_mode'] = $this->mode;
        $settings['setup_applied'] = $this->setupApplied;
        $settings['first_day_voting'] = $this->firstDayVoting;
        $this->room->settings = $settings;
        $this->room->save();
        $this->validateConfig();
    }

    public function validateConfig()
    {
        $effectiveCount = $this->expectedPlayerCount > 0 ? $this->expectedPlayerCount : $this->playerCount;

        $validator = app(RoleConfigValidator::class);
        $this->validationErrors = $validator->validate($effectiveCount, $this->roleCounts);
        $this->roleErrors = $validator->getPerRoleErrors($this->roleCounts);
        $this->warnings = $validator->getWarnings($effectiveCount, $this->roleCounts);
        $this->balanceStatus = $validator->getBalanceStatus($effectiveCount, $this->roleCounts);

        $totalAssigned = array_sum($this->roleCounts);
        if ($this->mode === 'advanced' && $effectiveCount > 0 && $totalAssigned !== $effectiveCount) {
            $this->validationErrors[] = __('ui.lobby.advanced_exact_count', ['expected' => $effectiveCount, 'assigned' => $totalAssigned]);
        }

        $this->canStart = empty($this->validationErrors) && $effectiveCount >= 4;
    }

    public function removePlayer(int $playerId)
    {
        $player = Player::findOrFail($playerId);
        event(new PlayerLeft($player));
        $player->delete();
        $this->refreshPlayerCount();
        $this->validateConfig();
    }

    public function copyLink()
    {
        $this->copied = 'link';
    }

    public function copyShareLink()
    {
        $this->copied = 'share';
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

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    }

    public function render()
    {
        $roles = Role::orderBy('faction')->orderBy('key')->get()->groupBy('faction');

        $this->refreshPlayerCount();
        $this->validateConfig();

        $effectiveCount = $this->expectedPlayerCount > 0 ? $this->expectedPlayerCount : $this->playerCount;
        $totalAssigned = array_sum($this->roleCounts);
        $remaining = max(0, $effectiveCount - $totalAssigned);
        $recommendedSetup = app(RoleConfigValidator::class)->getRecommendedSetup($this->expectedPlayerCount);

        return view('livewire.narrator.narrator-lobby', [
            'roles' => $roles,
            'players' => Player::where('room_id', $this->room->id)
                ->where('is_narrator', false)
                ->orderBy('created_at')
                ->get(),
            'effectiveCount' => $effectiveCount,
            'totalAssigned' => $totalAssigned,
            'remaining' => $remaining,
            'recommendedSetup' => $recommendedSetup,
            'explanations' => app(RoleConfigValidator::class)->getRecommendedSetupExplanation(),
        ])->layout('layouts.app');
    }
}
