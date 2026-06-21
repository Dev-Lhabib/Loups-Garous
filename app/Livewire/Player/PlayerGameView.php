<?php

namespace App\Livewire\Player;

use App\Events\SuspiciousAccessAttempt;
use App\Game\Services\ProgressTracker;
use App\Game\Services\VotingService;
use App\Models\GameState;
use App\Models\Player;
use App\Models\Room;
use Livewire\Component;

class PlayerGameView extends Component
{
    public Room $room;
    public Player $player;
    public ?GameState $state = null;
    public bool $ready = false;
    public bool $paused = false;
    public array $scapegoatDecreeBanned = [];
    public bool $scapegoatDecreeSubmitted = false;
    public bool $pendingHunterAction = false;
    public ?int $timerRemaining = null;

    public function mount(Room $room)
    {
        $requestPlayer = $this->resolvePlayerFromSession();

        if (!$requestPlayer || $requestPlayer->room_id !== $room->id || $requestPlayer->is_narrator) {
            if ($requestPlayer) {
                event(new SuspiciousAccessAttempt($requestPlayer, 'Non-player attempted game view'));
            }
            $this->redirect(route('home'));
            return;
        }

        if (!$room->gameState) {
            $this->redirect(route('lobby.player', $room));
            return;
        }

        $this->room = $room;
        $this->player = $requestPlayer->fresh(['role']);
        $this->state = $room->gameState;

        $data = $this->state?->data ?? [];
        $this->ready = in_array($this->player->id, $data['players_ready'] ?? []);
        $this->paused = !empty($data['paused']);
        $this->refreshTimer();
    }

    public function getListeners()
    {
        $roomId = $this->room->id;
        return [
            "echo-private:room.{$roomId},PhaseChanged" => 'onPhaseChanged',
            "echo-private:room.{$roomId},PlayerEliminated" => 'onPlayerEliminated',
            "echo-private:room.{$roomId},NightResolved" => 'onNightResolved',
            "echo-private:room.{$roomId},LoverDied" => 'onLoverDied',
            "echo-private:room.{$roomId},VillageIdiotRevealed" => 'onVillageIdiot',
            "echo-private:room.{$roomId},GameFinished" => 'onGameFinished',
            "echo-private:room.{$roomId},GameReset" => 'onGameReset',
            "echo-private:player.{$this->player->id},RoleAssigned" => 'onRoleAssigned',
            "echo-private:player.{$this->player->id},SeerResultReady" => 'onSeerResult',
            "echo-private:player.{$this->player->id},FoxResultReady" => 'onFoxResult',
            "echo-private:room.{$roomId},HunterActionPending" => 'onHunterActionPending',
            "echo-private:room.{$roomId},AllPlayersReady" => '$refresh',
            "echo-private:room.{$roomId},PlayerJoined" => '$refresh',
            "echo-private:room.{$roomId},PlayerLeft" => '$refresh',
            "echo-private:room.{$roomId},GamePaused" => 'onGamePaused',
        ];
    }

    public function ping(): void
    {
        $state = $this->room?->gameState;
        if (!$state) return;

        $data = $state->data;
        $data['player_heartbeats'] = $data['player_heartbeats'] ?? [];
        $data['player_heartbeats'][$this->player->id] = now()->toIso8601String();
        $state->data = $data;
        $state->save();
    }

    public function readyUp()
    {
        if ($this->paused) return;
        if (!$this->state) return;

        if ($this->ready) {
            $tracker = app(ProgressTracker::class);
            $tracker->markNotReady($this->player, $this->state);
            $this->ready = false;
            $this->state = $this->state->fresh();
            return;
        }

        $phase = $this->state->phase;
        if ($phase === 'waiting' && !$this->player->role) {
            return;
        }

        if ($phase === 'night' && !$this->player->is_alive) {
            return;
        }

        $tracker = app(ProgressTracker::class);
        $tracker->markReady($this->player, $this->state, $phase);
        $this->ready = true;
        $this->state = $this->state->fresh();
    }

    public function hydrate(): void
    {
        if (request()->hasHeader('X-Livewire')) {
            $room = $this->room ?? null;
            if ($room) {
                $fresh = Room::find($room->id);
                if (!$fresh || !$fresh->gameState) {
                    $this->state = null;
                }
            }
        }
    }

    public function onPlayerEliminated()
    {
        $this->player = $this->player->fresh(['role']);
        $this->state = $this->room->gameState;
    }

    public function onGameFinished()
    {
        $this->player = $this->player->fresh(['role']);
        $this->state = $this->room->gameState;
    }

    public function onRoleAssigned()
    {
        $this->player = $this->player->fresh(['role']);
    }

    public function onPhaseChanged(array $payload)
    {
        $gs = $this->room->gameState;
        if (!$gs) {
            $this->state = null;
            return;
        }
        $this->state = $gs->fresh();
        $this->player = $this->player->fresh(['role']);
        $this->ready = false;
        $this->refreshPaused();
        $this->refreshTimer();

        $phase = $this->state->phase;
        $this->dispatch('transition-phase',
            label: __("ui.phase.{$phase}"),
            subtitle: __("ui.phase.{$phase}_subtitle"),
            icon: match ($phase) { 'night' => '🌙', 'day' => '☀️', 'voting' => '🗳️', 'finished' => '🏆', default => '' },
            class: match ($phase) {
                'night' => 'phase-overlay phase-overlay-night',
                'day' => 'phase-overlay phase-overlay-day',
                'voting' => 'phase-overlay phase-overlay-voting',
                'finished' => 'phase-overlay phase-overlay-finished',
                default => 'phase-overlay phase-overlay-waiting',
            },
        );
    }

    public function onGamePaused(array $payload)
    {
        $this->paused = !empty($payload['paused']);
        $this->state = $this->room?->gameState;
    }

    public function dismissResult(string $type): void
    {
        if (!$this->state) return;
        $data = $this->state->data ?? [];
        unset($data[$type . '_results'][$this->player->id]);
        $this->state->data = $data;
        $this->state->save();
    }

    public function onSeerResult(array $payload)
    {
        $factionKey = $payload['faction'] ?? '';
        $this->dispatch('show-result', [
            'type' => 'seer',
            'nickname' => $payload['target_nickname'] ?? '',
            'faction' => __('ui.factions.' . $factionKey),
        ]);
    }

    public function onFoxResult(array $payload)
    {
        $this->dispatch('show-result', [
            'type' => 'fox',
            'found' => $payload['werewolf_found'] ?? false,
        ]);
    }

    public function onNightResolved(array $payload)
    {
        $this->state = $this->room?->gameState;
        $this->dispatch('show-night-resolved', [
            'eliminated' => $payload['eliminated'] ?? [],
        ]);
    }

    public function onLoverDied(array $payload)
    {
        $this->dispatch('show-result', [
            'type' => 'lover_died',
            'nickname' => $payload['nickname'] ?? '',
            'partner_nickname' => $payload['partner_nickname'] ?? '',
        ]);
    }

    public function onVillageIdiot(array $payload)
    {
        $this->dispatch('show-result', [
            'type' => 'village_idiot',
            'nickname' => $payload['nickname'] ?? '',
        ]);
    }

    public function onGameReset()
    {
        $this->js("window.location.href = '" . route('lobby.player', $this->room) . "'");
    }

    public function onHunterActionPending()
    {
        $this->state = $this->room->gameState;
        $data = $this->state->data ?? [];
        $this->pendingHunterAction = !empty($data['pending_hunter_action'])
            && ($data['pending_hunter_id'] ?? null) == $this->player->id;
    }

    public function submitHunterAction(string $targetId): void
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state) return;

        $data = $state->data ?? [];
        if (empty($data['pending_hunter_action'])) return;
        if (($data['pending_hunter_id'] ?? null) != $this->player->id) return;

        $engine = app(\App\Game\Engine\GameEngine::class);
        $engine->resolveHunterAction($state, $targetId);

        $this->pendingHunterAction = false;
        $this->state = $this->room->gameState;
    }

    public function resolveHunterTimeout(): void
    {
        $state = $this->room->gameState;
        if (!$state) return;

        $data = $state->data ?? [];
        if (empty($data['pending_hunter_action'])) return;

        $timeout = $data['pending_hunter_timeout'] ?? null;
        if ($timeout && now()->isBefore($timeout)) return;

        $engine = app(\App\Game\Engine\GameEngine::class);
        $engine->resolveHunterAction($state, null);

        $this->pendingHunterAction = false;
        $this->state = $this->room->gameState;
    }

    public function checkGameState(): void
    {
        if (!$this->room || !$this->room->id) return;

        $fresh = Room::find($this->room->id);
        if (!$fresh || $fresh->status === 'waiting' || !$fresh->gameState) {
            $this->redirect(route('lobby.player', $this->room));
        }
    }

    public function toggleDecreeBan(string $playerId): void
    {
        $idx = array_search($playerId, $this->scapegoatDecreeBanned);
        if ($idx !== false) {
            unset($this->scapegoatDecreeBanned[$idx]);
            $this->scapegoatDecreeBanned = array_values($this->scapegoatDecreeBanned);
        } else {
            $this->scapegoatDecreeBanned[] = $playerId;
        }
    }

    public function submitDecree(): void
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state) return;

        $data = $state->data ?? [];
        if (empty($data['scapegoat_decree_pending'])) return;
        if (($data['scapegoat_decree_player_id'] ?? null) !== $this->player->id) return;

        $votingService = app(VotingService::class);
        $winner = $votingService->submitScapegoatDecree($state, $this->scapegoatDecreeBanned);

        if ($winner) {
            $engine = app(\App\Game\Engine\GameEngine::class);
            $engine->endGame($state, $winner);
        }

        $this->scapegoatDecreeSubmitted = true;
        $this->state = $this->state?->fresh();
    }

    public function triggerSecondVote(): void
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'voting') return;

        $role = $this->player->role;
        if (!$role || $role->key !== 'stuttering_judge') return;

        $data = $state->data ?? [];
        if (!empty($data['stuttering_judge_used'])) return;

        $data['stuttering_judge_used'] = true;
        $data['second_vote_triggered'] = true;
        $state->data = $data;
        $state->save();

        \App\Models\Vote::where('game_state_id', $state->id)->delete();

        $this->state = $this->state?->fresh();
    }

    public function acceptSwap(): void
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state) return;

        $data = $state->data ?? [];
        if (empty($data['devoted_servant_swap_pending'])) return;

        $role = $this->player->role;
        if (!$role || $role->key !== 'devoted_servant') abort(403);

        $votingService = app(VotingService::class);
        $winner = $votingService->acceptDevotedServantSwap($state, $this->player);

        if ($winner) {
            $engine = app(\App\Game\Engine\GameEngine::class);
            $engine->endGame($state, $winner);
        }

        $this->player = $this->player->fresh(['role']);
        $this->state = $this->state?->fresh();
    }

    public function declineSwap(): void
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state) return;

        $data = $state->data ?? [];
        if (empty($data['devoted_servant_swap_pending'])) return;

        $role = $this->player->role;
        if (!$role || $role->key !== 'devoted_servant') abort(403);

        $votingService = app(VotingService::class);
        $winner = $votingService->declineDevotedServantSwap($state);

        if ($winner) {
            $engine = app(\App\Game\Engine\GameEngine::class);
            $engine->endGame($state, $winner);
        }

        $this->player = $this->player->fresh(['role']);
        $this->state = $this->state?->fresh();
    }

    private function refreshPaused(): void
    {
        $data = $this->state?->data ?? [];
        $this->paused = !empty($data['paused']);
    }

    private function refreshTimer(): void
    {
        if (!$this->state) return;
        $phase = $this->state->phase;
        if (in_array($phase, ['waiting', 'finished'], true)) {
            $this->timerRemaining = null;
            return;
        }

        $data = $this->state->data ?? [];
        $timerKey = "{$phase}_timer_started_at";
        $configKey = "{$phase}_timer_config";

        $startedAt = $data[$timerKey] ?? null;
        $configSeconds = $data[$configKey] ?? 0;

        if ($startedAt && $configSeconds > 0) {
            $elapsed = now()->diffInSeconds(\Carbon\Carbon::parse($startedAt));
            $this->timerRemaining = max(0, $configSeconds - $elapsed);
        } else {
            $this->timerRemaining = null;
        }
    }

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    }

    public function render()
    {
        $this->state = $this->room?->gameState;

        if (!$this->state) {
            if (!request()->hasHeader('X-Livewire')) {
                $this->redirect(route('lobby.player', $this->room));
            }
            $this->state = $this->room?->gameState;
            if (!$this->state) {
                return view('livewire.player.player-game-view', [
                    'state' => null,
                    'players' => collect(),
                    'playersAliveCount' => 0,
                    'playersTotalCount' => 0,
                    'pendingHunterAction' => false,
                    'hunterAlivePlayers' => collect(),
                    'paused' => false,
                    'timerRemaining' => null,
                    'ready' => false,
                    'isSequentialNight' => false,
                    'activeNightRole' => null,
                    'canActNow' => true,
                    'nightProgress' => [],
                    'nightProgressTotal' => 0,
                    'nightProgressDone' => 0,
                ])->layout('layouts.app');
            }
        }

        if (!$this->player->relationLoaded('role')) {
            $this->player->load('role');
        }

        $data = $this->state->data ?? [];
        $this->pendingHunterAction = !empty($data['pending_hunter_action'])
            && ($data['pending_hunter_id'] ?? null) == $this->player->id;
        $this->paused = !empty($data['paused']);
        $this->refreshTimer();
        $this->ready = in_array($this->player->id, $data['players_ready'] ?? []);

        $playersAliveCount = Player::where('room_id', $this->room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->count();

        $playersTotalCount = Player::where('room_id', $this->room->id)
            ->where('is_narrator', false)
            ->count();

        $players = collect();
        $hunterAlivePlayers = collect();
        if ($this->state->phase === 'finished') {
            $players = Player::where('room_id', $this->room->id)
                ->where('is_narrator', false)
                ->with('role')
                ->orderBy('created_at')
                ->get();
        }

        if ($this->pendingHunterAction) {
            $hunterAlivePlayers = Player::where('room_id', $this->room->id)
                ->where('is_alive', true)
                ->where('is_narrator', false)
                ->where('id', '!=', $this->player->id)
                ->orderBy('nickname')
                ->get();
        }

        $isSequentialNight = ($data['night_mode'] ?? 'parallel') === 'sequential' && $this->state->phase === 'night';
        $activeNightRole = $data['active_night_role'] ?? null;

        $canActNow = true;
        if ($isSequentialNight && $this->player->role && $this->player->role->night_order !== null) {
            $canActNow = $activeNightRole === $this->player->role->key;
        }

        $nightProgress = [];
        $nightProgressTotal = 0;
        $nightProgressDone = 0;
        if ($this->state->phase === 'night') {
            $progressTracker = app(ProgressTracker::class);
            $nightProgress = $progressTracker->getNightActionProgress($this->state);
            foreach ($nightProgress as $rp) {
                $nightProgressTotal += $rp['total'];
                $nightProgressDone += $rp['done'];
            }
        }

        return view('livewire.player.player-game-view', [
            'state' => $this->state,
            'players' => $players,
            'playersAliveCount' => $playersAliveCount,
            'playersTotalCount' => $playersTotalCount,
            'pendingHunterAction' => $this->pendingHunterAction,
            'hunterAlivePlayers' => $hunterAlivePlayers,
            'paused' => $this->paused,
            'timerRemaining' => $this->timerRemaining,
            'ready' => $this->ready,
            'isSequentialNight' => $isSequentialNight,
            'activeNightRole' => $activeNightRole,
            'canActNow' => $canActNow,
            'nightProgress' => $nightProgress,
            'nightProgressTotal' => $nightProgressTotal,
            'nightProgressDone' => $nightProgressDone,
        ])->layout('layouts.app');
    }
}
