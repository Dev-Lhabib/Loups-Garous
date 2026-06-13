<?php

namespace App\Livewire\Narrator;

use App\Events\GameReset;
use App\Events\SuspiciousAccessAttempt;
use App\Game\Engine\GameEngine;
use App\Game\Engine\PhaseManager;
use App\Models\CoupleBond;
use App\Models\GameState;
use App\Models\NightAction;
use App\Models\Player;
use App\Models\Room;
use App\Models\Vote;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class NarratorDashboard extends Component
{
    public Room $room;
    public GameState $state;
    public Player $player;
    public array $gameLog = [];
    public array $nightActionFeed = [];
    public array $pendingRoles = [];

    public function mount(Room $room)
    {
        $requestPlayer = request()->get('_player');

        if (!$requestPlayer || !$requestPlayer->is_narrator || $requestPlayer->room_id !== $room->id) {
            if ($requestPlayer) {
                event(new SuspiciousAccessAttempt($requestPlayer, 'Non-narrator attempted narrator dashboard'));
            }
            $this->redirect(route('home'));
            return;
        }

        if ($room->status === 'waiting') {
            $this->redirect(route('lobby.narrator', $room));
            return;
        }

        $this->room = $room;
        $this->player = $requestPlayer;
        $this->state = $room->gameState;
        $this->initGameLog();
        $this->refreshNightFeed();
    }

    public function advancePhase(string $toPhase)
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || !$requestPlayer->is_narrator || $requestPlayer->room_id !== $this->room->id) {
            event(new SuspiciousAccessAttempt($requestPlayer ?? $this->player, 'Non-narrator attempted phase transition'));
            $this->redirect(route('home'));
            return;
        }

        try {
            $engine = app(GameEngine::class);

            if ($this->state->phase === 'night' && $toPhase === 'day') {
                $this->clearNightTimers();
                $engine->resolveNight($this->state);
                $this->addLogEntry('night_resolved', []);
            } elseif ($this->state->phase === 'voting' && $toPhase === 'night') {
                $this->clearNightTimers();
                $engine->resolveVote($this->state);
                $this->addLogEntry('voting_resolved', []);
            } else {
                $this->clearNightTimers();
                $engine->advancePhase($this->state, $toPhase);
            }

            $this->state = $this->state->fresh();
            $this->addLogEntry('phase_changed', ['from' => $this->state->phase, 'to' => $toPhase]);
            $this->refreshNightFeed();

            if ($toPhase === 'night') {
                $this->initNightTimer();
            }
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function forceResolve()
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || !$requestPlayer->is_narrator || $requestPlayer->room_id !== $this->room->id) {
            event(new SuspiciousAccessAttempt($requestPlayer ?? $this->player, 'Non-narrator attempted force resolve'));
            $this->redirect(route('home'));
            return;
        }

        if ($this->state->phase !== 'night') return;

        $this->clearNightTimers();
        $engine = app(GameEngine::class);
        $engine->resolveNight($this->state);

        $this->state = $this->state->fresh();
        $this->addLogEntry('night_resolved', []);
        $this->addLogEntry('phase_changed', ['from' => 'night', 'to' => 'day']);
        $this->refreshNightFeed();
    }

    public function littleGirlCaught()
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || !$requestPlayer->is_narrator || $requestPlayer->room_id !== $this->room->id) {
            event(new SuspiciousAccessAttempt($requestPlayer ?? $this->player, 'Non-narrator attempted little girl caught'));
            $this->redirect(route('home'));
            return;
        }

        if ($this->state->phase !== 'night') return;

        $littleGirl = Player::where('room_id', $this->room->id)
            ->where('is_alive', true)
            ->whereHas('role', fn ($q) => $q->where('key', 'little_girl'))
            ->first();

        if (!$littleGirl) return;

        $votingService = app(\App\Game\Services\VotingService::class);
        $winner = $votingService->applyDeathWithChain($this->state, $littleGirl);

        if ($winner) {
            $engine = app(GameEngine::class);
            $engine->endGame($this->state, $winner);
        }

        $this->state = $this->state->fresh();
        $this->addLogEntry('player_eliminated', ['nickname' => $littleGirl->nickname]);
        $this->refreshNightFeed();
    }

    public function newGame()
    {
        $requestPlayer = request()->get('_player');
        if (!$requestPlayer || !$requestPlayer->is_narrator || $requestPlayer->room_id !== $this->room->id) {
            event(new SuspiciousAccessAttempt($requestPlayer ?? $this->player, 'Non-narrator attempted new game'));
            $this->redirect(route('home'));
            return;
        }

        $room = $this->room;

        DB::transaction(function () use ($room) {
            if ($room->gameState) {
                $stateId = $room->gameState->id;
                NightAction::where('game_state_id', $stateId)->delete();
                Vote::where('game_state_id', $stateId)->delete();
                CoupleBond::where('game_state_id', $stateId)->delete();
                GameState::where('room_id', $room->id)->delete();
            }

            Player::where('room_id', $room->id)->update([
                'role_id' => null,
                'is_alive' => true,
                'voting_banned' => false,
            ]);

            $room->status = 'waiting';
            $room->save();
        });

        event(new GameReset($room));

        $this->redirect(route('lobby.narrator', $room));
    }

    private function initNightTimer()
    {
        $data = $this->state->data ?? [];
        $data['night_started_at'] = now()->toIso8601String();
        $data['night_timeout_seconds'] = 120;
        $data['auto_resolve_at'] = null;
        $data['player_heartbeats'] = $data['player_heartbeats'] ?? [];
        $this->state->data = $data;
        $this->state->save();
    }

    private function clearNightTimers()
    {
        $data = $this->state->data ?? [];
        unset($data['night_started_at']);
        unset($data['auto_resolve_at']);
        $this->state->data = $data;
        $this->state->save();
    }

    private function initGameLog()
    {
        $this->gameLog = [];
        $this->addLogEntry('game_started', ['round' => $this->state->round]);
    }

    private function addLogEntry(string $type, array $data)
    {
        $this->gameLog[] = array_merge([
            'type' => $type,
            'round' => $this->state->round ?? 1,
            'phase' => $this->state->phase ?? 'waiting',
            'timestamp' => now()->toIso8601String(),
        ], $data);

        if (count($this->gameLog) > 200) {
            array_shift($this->gameLog);
        }
    }

    private function refreshNightFeed()
    {
        if ($this->state->phase !== 'night') {
            $this->nightActionFeed = [];
            $this->pendingRoles = [];
            return;
        }

        $actions = NightAction::where('game_state_id', $this->state->id)
            ->whereNull('resolved_at')
            ->with(['player.role', 'target'])
            ->orderBy('created_at')
            ->get();

        $this->nightActionFeed = $actions->map(function ($a) {
            return [
                'id' => $a->id,
                'role_key' => $a->player->role?->key,
                'action_type' => $a->action_type,
                'target_nickname' => $a->target?->nickname,
                'player_nickname' => $a->player->nickname,
                'player_id' => $a->player_id,
                'timestamp' => $a->created_at->toIso8601String(),
            ];
        })->toArray();

        $rolesWithActions = [
            'cupid', 'wolf_hound', 'werewolf', 'big_bad_wolf', 'accursed_wolf_father',
            'white_werewolf', 'bodyguard', 'seer', 'witch', 'pied_piper', 'fox',
        ];

        $submittedPlayerIds = $actions->pluck('player_id')->toArray();
        $allAlive = Player::where('room_id', $this->room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->with('role')
            ->get();

        $pending = [];
        foreach ($allAlive as $p) {
            if ($p->role && in_array($p->role->key, $rolesWithActions)) {
                if (!in_array($p->id, $submittedPlayerIds)) {
                    $pending[] = [
                        'role_key' => $p->role->key,
                        'player_id' => $p->id,
                        'player_nickname' => $p->nickname,
                    ];
                }
            }
        }
        $this->pendingRoles = $pending;
    }

    private function checkHeartbeats(): array
    {
        $data = $this->state->data ?? [];
        $heartbeats = $data['player_heartbeats'] ?? [];
        $disconnected = [];
        $forceKilled = false;

        $alivePlayers = Player::where('room_id', $this->room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->get();

        foreach ($alivePlayers as $p) {
            $lastPing = $heartbeats[$p->id] ?? null;
            if ($lastPing) {
                $elapsed = now()->diffInSeconds(\Carbon\Carbon::parse($lastPing));
                if ($elapsed > 60) {
                    $disconnected[] = [
                        'id' => $p->id,
                        'nickname' => $p->nickname,
                        'last_ping' => $lastPing,
                        'elapsed' => $elapsed,
                    ];

                    if ($elapsed > 120) {
                        $this->forceKillDisconnected($p, $data);
                        $forceKilled = true;
                    }
                }
            }
        }

        return $disconnected;
    }

    private function forceKillDisconnected(Player $player, array &$data): void
    {
        $player->is_alive = false;
        $player->save();

        $disconnectedPlayers = $data['disconnected_players'] ?? [];
        $disconnectedPlayers[] = [
            'player_id' => $player->id,
            'nickname' => $player->nickname,
            'disconnected_at' => now()->toIso8601String(),
            'force_killed' => true,
        ];
        $data['disconnected_players'] = $disconnectedPlayers;
        $this->state->data = $data;
        $this->state->save();

        event(new \App\Events\PlayerEliminated($player));

        $winChecker = app(\App\Game\Engine\WinConditionChecker::class);
        $winner = $winChecker->check($this->state);
        if ($winner) {
            $engine = app(GameEngine::class);
            $engine->endGame($this->state, $winner);
        }
    }

    public function getNightElapsed(): int
    {
        $data = $this->state->data ?? [];
        $startedAt = $data['night_started_at'] ?? null;
        if (!$startedAt) return 0;
        return now()->diffInSeconds(\Carbon\Carbon::parse($startedAt));
    }

    public function getNightRemaining(): int
    {
        $data = $this->state->data ?? [];
        $startedAt = $data['night_started_at'] ?? null;
        $timeout = $data['night_timeout_seconds'] ?? 120;
        if (!$startedAt) return $timeout;
        $elapsed = now()->diffInSeconds(\Carbon\Carbon::parse($startedAt));
        return max(0, $timeout - $elapsed);
    }

    public function getAutoResolveTimeLeft(): ?int
    {
        $data = $this->state->data ?? [];
        $autoResolveAt = $data['auto_resolve_at'] ?? null;
        if (!$autoResolveAt) return null;
        return max(0, now()->diffInSeconds(\Carbon\Carbon::parse($autoResolveAt), false));
    }

    public function getListeners()
    {
        return [
            "echo-private:room.{$this->room->id},PhaseChanged" => 'onPhaseChanged',
            "echo-private:room.{$this->room->id},PlayerEliminated" => 'onPlayerEliminated',
            "echo-private:narrator.{$this->room->id},NightActionSubmitted" => 'onNightActionSubmitted',
            "echo-private:narrator.{$this->room->id},VoteSubmitted" => 'onVoteSubmitted',
            "echo-private:narrator.{$this->room->id},SuspiciousAccessAttempt" => 'onSuspiciousAccess',
            "echo-private:room.{$this->room->id},GameFinished" => 'onGameFinished',
            "echo-private:room.{$this->room->id},AllPlayersReady" => 'onAllPlayersReady',
        ];
    }

    public function onPhaseChanged()
    {
        $this->state = $this->state->fresh();
        $this->refreshNightFeed();

        if ($this->state->phase === 'night') {
            $this->initNightTimer();
        } else {
            $this->clearNightTimers();
        }

        $phase = $this->state->phase;
        $labels = [
            'waiting' => __('ui.phase.waiting'),
            'night' => __('ui.phase.night'),
            'day' => __('ui.phase.day'),
            'voting' => __('ui.phase.voting'),
            'finished' => __('ui.phase.finished'),
        ];
        $subtitles = [
            'night' => __('ui.phase.night_subtitle'),
            'day' => __('ui.phase.day_subtitle'),
            'voting' => __('ui.phase.voting_subtitle'),
            'finished' => __('ui.phase.finished_subtitle'),
        ];
        $icons = [
            'night' => '🌙',
            'day' => '☀️',
            'voting' => '🗳️',
            'finished' => '🏆',
        ];
        $classes = [
            'waiting' => 'phase-overlay phase-overlay-waiting',
            'night' => 'phase-overlay phase-overlay-night',
            'day' => 'phase-overlay phase-overlay-day',
            'voting' => 'phase-overlay phase-overlay-voting',
            'finished' => 'phase-overlay phase-overlay-finished',
        ];
        $this->dispatch('transition-phase',
            label: $labels[$phase] ?? '',
            subtitle: $subtitles[$phase] ?? '',
            icon: $icons[$phase] ?? '',
            class: $classes[$phase] ?? '',
        );
    }

    public function onPlayerEliminated($payload)
    {
        $this->addLogEntry('player_eliminated', [
            'nickname' => $payload['nickname'] ?? 'unknown',
        ]);
    }

    public function onNightActionSubmitted()
    {
        $this->refreshNightFeed();
        $this->checkAutoResolve();
    }

    public function onVoteSubmitted()
    {
        $this->addLogEntry('vote_submitted', []);
    }

    public function onSuspiciousAccess($payload)
    {
        $this->addLogEntry('suspicious_access', [
            'player_nickname' => $payload['player']['nickname'] ?? 'unknown',
            'details' => $payload['details'] ?? '',
        ]);
    }

    public function onGameFinished($payload)
    {
        $this->state = $this->state->fresh();
        $this->clearNightTimers();
        $this->addLogEntry('game_finished', [
            'winning_faction' => $payload['winning_faction'] ?? 'unknown',
        ]);
    }

    public function onAllPlayersReady()
    {
        $this->addLogEntry('all_players_ready', []);
        $this->advancePhase('night');
    }

    public function checkAutoResolve(): void
    {
        if ($this->state->phase !== 'night') return;

        if (empty($this->pendingRoles)) {
            $data = $this->state->data ?? [];
            if (!isset($data['auto_resolve_at'])) {
                $data['auto_resolve_at'] = now()->addSeconds(3)->toIso8601String();
                $this->state->data = $data;
                $this->state->save();
                $this->dispatch('auto-resolve-countdown', seconds: 3);
            }
        } else {
            $data = $this->state->data ?? [];
            if (isset($data['auto_resolve_at'])) {
                unset($data['auto_resolve_at']);
                $this->state->data = $data;
                $this->state->save();
            }
        }
    }

    public function tick(): void
    {
        if ($this->state->phase !== 'night') return;

        $this->refreshNightFeed();

        $data = $this->state->data ?? [];

        // Check auto-resolve (all actions submitted)
        $autoResolveAt = $data['auto_resolve_at'] ?? null;
        if ($autoResolveAt && now() >= \Carbon\Carbon::parse($autoResolveAt)) {
            $this->clearNightTimers();
            $engine = app(GameEngine::class);
            $engine->resolveNight($this->state);
            $this->state = $this->state->fresh();
            $this->addLogEntry('night_resolved', []);
            $this->addLogEntry('phase_changed', ['from' => 'night', 'to' => 'day']);
            $this->refreshNightFeed();
            return;
        }

        // Check timeout
        $nightStartedAt = $data['night_started_at'] ?? null;
        $timeout = $data['night_timeout_seconds'] ?? 120;
        if ($nightStartedAt && (now()->diffInSeconds(\Carbon\Carbon::parse($nightStartedAt)) >= $timeout)) {
            if (!empty($this->pendingRoles)) {
                $this->addLogEntry('night_resolved', []);
            }
            $this->clearNightTimers();
            $engine = app(GameEngine::class);
            $engine->resolveNight($this->state);
            $this->state = $this->state->fresh();
            $this->addLogEntry('phase_changed', ['from' => 'night', 'to' => 'day']);
            $this->refreshNightFeed();
        }
    }

    public function render()
    {
        $this->refreshNightFeed();

        $players = Player::where('room_id', $this->room->id)
            ->where('is_narrator', false)
            ->with('role')
            ->orderBy('created_at')
            ->get();

        $phase = $this->state->phase;
        $availableTransitions = $this->getTransitions($phase);

        $voteTally = [];
        $voteCount = 0;
        if ($phase === 'voting') {
            $votes = Vote::where('game_state_id', $this->state->id)->get();
            $voteCount = $votes->count();
            foreach ($votes as $v) {
                $voteTally[$v->target_id] = ($voteTally[$v->target_id] ?? 0) + 1;
            }
            arsort($voteTally);
        }

        $totalAlive = $players->where('is_alive', true)->count();

        $coupleBonds = CoupleBond::where('game_state_id', $this->state->id)->get();
        $loverMap = [];
        foreach ($coupleBonds as $bond) {
            $loverMap[$bond->player_id] = $bond->partner_id;
            $loverMap[$bond->partner_id] = $bond->player_id;
        }

        $enchantedIds = $this->state->data['enchanted_player_ids'] ?? [];

        $nightOrder = [
            'cupid', 'wolf_hound', 'werewolf', 'big_bad_wolf',
            'accursed_wolf_father', 'white_werewolf', 'bodyguard',
            'little_girl', 'seer', 'witch', 'pied_piper', 'fox', 'bear_tamer',
        ];

        $actionHistory = $this->state->data['action_history'] ?? [];

        $disconnectedPlayers = $this->checkHeartbeats();
        $nightElapsed = $this->getNightElapsed();
        $nightRemaining = $this->getNightRemaining();
        $autoResolveTimeLeft = $this->getAutoResolveTimeLeft();

        $pendingRoleKeys = array_unique(array_column($this->pendingRoles, 'role_key'));
        $completedRoleKeys = array_unique(array_column($this->nightActionFeed, 'role_key'));

        $littleGirlAlive = $players->contains(function ($p) {
            return $p->is_alive && $p->role && $p->role->key === 'little_girl';
        });

        $bearTamerGrowl = $this->checkBearTamerGrowl($players);

        return view('livewire.narrator.narrator-dashboard', [
            'players' => $players,
            'availableTransitions' => $availableTransitions,
            'voteTally' => $voteTally,
            'voteCount' => $voteCount,
            'totalAlive' => $totalAlive,
            'loverMap' => $loverMap,
            'enchantedIds' => $enchantedIds,
            'nightOrder' => $nightOrder,
            'state' => $this->state,
            'actionHistory' => $actionHistory,
            'disconnectedPlayers' => $disconnectedPlayers,
            'nightElapsed' => $nightElapsed,
            'nightRemaining' => $nightRemaining,
            'autoResolveTimeLeft' => $autoResolveTimeLeft,
            'pendingRoleKeys' => $pendingRoleKeys,
            'completedRoleKeys' => $completedRoleKeys,
            'littleGirlAlive' => $littleGirlAlive,
            'bearTamerGrowl' => $bearTamerGrowl,
        ])->layout('layouts.app');
    }

    private function checkBearTamerGrowl($players): ?array
    {
        $data = $this->state->data ?? [];
        if (empty($data['bear_tamer_alive'])) return null;

        $bearTamer = $players->first(function ($p) {
            return $p->is_alive && $p->role && $p->role->key === 'bear_tamer';
        });
        if (!$bearTamer) return null;

        $seatOrder = $data['seat_order'] ?? [];
        if (empty($seatOrder)) return null;

        $position = array_search($bearTamer->id, $seatOrder);
        if ($position === false) return null;

        $count = count($seatOrder);
        $leftIndex = ($position - 1 + $count) % $count;
        $rightIndex = ($position + 1) % $count;

        $adjacentIds = [$seatOrder[$leftIndex], $seatOrder[$rightIndex]];

        $werewolfKeys = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf'];

        $adjacentWerewolves = [];
        foreach ($adjacentIds as $adjId) {
            $adjPlayer = $players->firstWhere('id', $adjId);
            if ($adjPlayer && $adjPlayer->is_alive && $adjPlayer->role && in_array($adjPlayer->role->key, $werewolfKeys)) {
                $adjacentWerewolves[] = $adjPlayer->nickname;
            }
        }

        if (empty($adjacentWerewolves)) return null;

        return [
            'growls' => true,
            'werewolf_count' => count($adjacentWerewolves),
        ];
    }

    private function getTransitions(string $phase): array
    {
        return match ($phase) {
            'night' => ['day', 'finished'],
            'day' => ['voting', 'night'],
            'voting' => ['night', 'finished'],
            'finished' => [],
            default => [],
        };
    }
}
