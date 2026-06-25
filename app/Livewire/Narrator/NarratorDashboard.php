<?php

namespace App\Livewire\Narrator;

use App\Events\GameReset;
use App\Events\SuspiciousAccessAttempt;
use App\Events\GameFinished;
use App\Game\Engine\GameEngine;
use App\Game\Services\NarratorControlService;
use App\Game\Services\ProgressTracker;
use App\Models\CoupleBond;
use App\Models\GameState;
use App\Models\NightAction;
use App\Models\Player;
use App\Models\Room;
use App\Models\Vote;
use Livewire\Component;

class NarratorDashboard extends Component
{
    public Room $room;
    public GameState $state;
    public Player $player;
    public array $gameLog = [];
    public array $nightActionFeed = [];
    public array $pendingRoles = [];
    public string $sidebarTab = 'status';
    public string $nightMode = 'parallel';
    public bool $showTimerConfig = false;
    public bool $votingTransitionNeeded = false;
    public int $timerNightSeconds = 120;
    public int $timerDiscussionSeconds = 180;
    public int $timerVotingSeconds = 60;

    public function mount(Room $room)
    {
        $requestPlayer = $this->resolvePlayerFromSession();

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
        $this->nightMode = $this->state->data['night_mode'] ?? 'parallel';
    }

    public function startNight()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $service->setNightMode($this->state, $this->nightMode);
        $service->advancePhase($this->state, 'night');
        $this->state = $this->state->fresh();
        $this->addLogEntry('phase_changed', ['from' => $this->state->phase, 'to' => 'night']);
        $this->refreshNightFeed();
    }

    public function endNight()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);

        $this->autoSkipPendingPlayers();

        $service->resolveNightOnly($this->state);
        $this->state = $this->state->fresh();
        $this->refreshNightFeed();

        if (($this->state->data['winning_faction'] ?? null) !== null) {
            $this->addLogEntry('game_finished', ['winning_faction' => $this->state->data['winning_faction']]);
            return;
        }

        $this->addLogEntry('night_resolved', []);

        $winner = $this->checkWinCondition();
        if ($winner) {
            return;
        }

        $service->advancePhase($this->state, 'day');
        $this->state = $this->state->fresh();
        $this->addLogEntry('phase_changed', ['from' => 'night', 'to' => 'day']);
    }

    public function startDay()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $service->advancePhase($this->state, 'day');
        $this->state = $this->state->fresh();
        $this->addLogEntry('phase_changed', ['from' => $this->state->phase, 'to' => 'day']);
    }

    public function startVoting()
    {
        $this->guardNarrator();

        $firstDayVoting = $this->room->settings['first_day_voting'] ?? true;
        if (!$firstDayVoting && $this->state->round === 1) {
            $this->addLogEntry('phase_blocked', ['reason' => 'first_day_voting_disabled']);
            session()->flash('error', __('ui.narrator.first_day_voting_blocked'));
            return;
        }

        $service = app(NarratorControlService::class);
        $service->advancePhase($this->state, 'voting');
        $this->state = $this->state->fresh();
        $this->addLogEntry('phase_changed', ['from' => $this->state->phase, 'to' => 'voting']);
    }

    public function endVoting()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $service->resolveVoteOnly($this->state);
        $this->state = $this->state->fresh();

        if (($this->state->data['winning_faction'] ?? null) !== null) {
            $this->addLogEntry('game_finished', ['winning_faction' => $this->state->data['winning_faction']]);
            return;
        }

        $this->addLogEntry('voting_resolved', []);

        $winner = $this->checkWinCondition();
        if ($winner) {
            return;
        }

        $data = $this->state->data ?? [];
        $secondVote = $data['second_vote_triggered'] ?? false;
        if ($secondVote) {
            $data['second_vote_triggered'] = false;
            $this->state->data = $data;
            $this->state->save();
            $this->votingTransitionNeeded = true;
        } else {
            $this->votingTransitionNeeded = true;
        }
    }

    public function goToNightAfterVote()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $service->advancePhase($this->state, 'night');
        $this->state = $this->state->fresh();
        $this->votingTransitionNeeded = false;
        $this->addLogEntry('phase_changed', ['from' => 'voting', 'to' => 'night']);
    }

    public function endGame()
    {
        $this->guardNarrator();
        if ($this->state->phase === 'finished') return;

        $data = $this->state->data;
        $data['winning_faction'] = 'no_one';
        $this->state->data = $data;
        $this->state->save();

        $this->room->status = 'finished';
        $this->room->save();

        $phaseManager = app(\App\Game\Engine\PhaseManager::class);
        $phaseManager->transition($this->state, 'finished');

        $this->state = $this->state->fresh();
        $this->addLogEntry('game_finished', ['winning_faction' => 'no_one']);
    }

    public function pauseGame()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $paused = $service->togglePause($this->state);
        $this->state = $this->state->fresh();
        $this->addLogEntry($paused ? 'game_paused' : 'game_resumed', []);
    }

    public function activateNextRole()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $nextRole = $service->activateNextRole($this->state);
        $this->state = $this->state->fresh();

        if ($nextRole) {
            $this->addLogEntry('night_role_activated', ['role' => $nextRole]);
        }

        $this->refreshNightFeed();
    }

    public function skipCurrentNightRole()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $nextRole = $service->skipCurrentNightRole($this->state);
        $this->state = $this->state->fresh();

        if ($nextRole) {
            $this->addLogEntry('night_role_activated', ['role' => $nextRole]);
        } else {
            $this->addLogEntry('night_sequence_complete', []);
        }

        $this->refreshNightFeed();
    }

    public function skipPlayer($playerId)
    {
        $this->guardNarrator();
        $player = Player::findOrFail($playerId);
        $service = app(NarratorControlService::class);
        $service->skipPlayerNightAction($player, $this->state);
        $this->state = $this->state->fresh();
        $this->addLogEntry('player_skipped', ['nickname' => $player->nickname]);
        $this->refreshNightFeed();
    }

    private function autoSkipPendingPlayers(): void
    {
        $service = app(NarratorControlService::class);
        foreach ($this->pendingRoles as $pending) {
            $player = Player::find($pending['player_id']);
            if ($player && $player->is_alive) {
                $service->skipPlayerNightAction($player, $this->state);
            }
        }
    }

    public function forceEndNight()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);

        $this->autoSkipPendingPlayers();

        $service->forceEndNight($this->state);
        $this->state = $this->state->fresh();
        $this->refreshNightFeed();

        if (($this->state->data['winning_faction'] ?? null) !== null) {
            $this->addLogEntry('game_finished', ['winning_faction' => $this->state->data['winning_faction']]);
            return;
        }

        $winner = $this->checkWinCondition();
        if ($winner) {
            return;
        }

        $service->advancePhase($this->state, 'day');
        $this->state = $this->state->fresh();
        $this->addLogEntry('phase_changed', ['from' => 'night', 'to' => 'day']);
    }

    public function setNightMode(string $mode)
    {
        if (!in_array($mode, ['sequential', 'parallel'], true)) return;
        $this->nightMode = $mode;
    }

    public function startTimer()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $seconds = match ($this->state->phase) {
            'night' => $this->timerNightSeconds,
            'day' => $this->timerDiscussionSeconds,
            'voting' => $this->timerVotingSeconds,
            default => 0,
        };
        if ($seconds > 0) {
            $service->startTimer($this->state, $this->state->phase, $seconds);
            $this->state = $this->state->fresh();
            $this->addLogEntry('timer_started', ['seconds' => $seconds]);
        }
    }

    public function extendTimer(int $seconds)
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $service->extendTimer($this->state, $seconds);
        $this->state = $this->state->fresh();
        $this->addLogEntry('timer_extended', ['seconds' => $seconds]);
    }

    public function dismissTimer()
    {
        $this->guardNarrator();
        $service = app(NarratorControlService::class);
        $service->dismissTimer($this->state);
        $this->state = $this->state->fresh();
        $this->addLogEntry('timer_dismissed', []);
    }

    public function littleGirlCaught()
    {
        $this->guardNarrator();
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
        $this->addLogEntry('player_eliminated', ['nickname' => $littleGirl->nickname, 'cause_key' => 'eliminated_by_little_girl']);
        $this->refreshNightFeed();
    }

    public function newGame()
    {
        $this->guardNarrator();
        $room = $this->room;

        \Illuminate\Support\Facades\DB::transaction(function () use ($room) {
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

    public function tick(): void
    {
        $this->state = $this->state->fresh();

        if (($this->state->data['winning_faction'] ?? null) !== null) {
            return;
        }

        $data = $this->state->data ?? [];
        if (!empty($data['paused'])) return;

        $this->checkTimerExpiry();

        $this->refreshNightFeed();
    }

    private function checkTimerExpiry(): void
    {
        $phase = $this->state->phase;
        if (in_array($phase, ['waiting', 'finished'], true)) return;

        $data = $this->state->data ?? [];
        $timerKey = "{$phase}_timer_started_at";
        $configKey = "{$phase}_timer_config";
        $expiredKey = "{$phase}_timer_expired";

        $startedAt = $data[$timerKey] ?? null;
        $configSeconds = $data[$configKey] ?? 0;

        if (!$startedAt || $configSeconds <= 0) return;
        if (!empty($data[$expiredKey])) return;

        $elapsed = now()->diffInSeconds(\Carbon\Carbon::parse($startedAt));
        if ($elapsed >= $configSeconds) {
            $data[$expiredKey] = true;
            $this->state->data = $data;
            $this->state->save();
            $this->addLogEntry('timer_expired', []);
            $this->dispatch('timer-expired');
        }
    }

    private function checkWinCondition(): ?\App\Game\Factions\FactionInterface
    {
        $winChecker = app(\App\Game\Engine\WinConditionChecker::class);
        $winner = $winChecker->check($this->state);
        if ($winner) {
            $engine = app(GameEngine::class);
            $engine->endGame($this->state, $winner);
            $this->state = $this->state->fresh();
            $this->addLogEntry('game_finished', ['winning_faction' => $winner->getKey()]);
            return $winner;
        }
        return null;
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

    public function getListeners()
    {
        return [
            "echo-private:room.{$this->room->id},PhaseChanged" => 'onPhaseChanged',
            "echo-private:room.{$this->room->id},PlayerEliminated" => 'onPlayerEliminated',
            "echo-private:room.{$this->room->id},GamePaused" => 'onGamePaused',
            "echo-private:narrator.{$this->room->id},NightActionSubmitted" => 'onNightActionSubmitted',
            "echo-private:narrator.{$this->room->id},VoteSubmitted" => 'onVoteSubmitted',
            "echo-private:narrator.{$this->room->id},SuspiciousAccessAttempt" => 'onSuspiciousAccess',
            "echo-private:room.{$this->room->id},GameFinished" => 'onGameFinished',
        ];
    }

    public function onGamePaused(array $payload)
    {
        $this->state = $this->state->fresh();
        $paused = !empty($payload['paused']);
        $this->addLogEntry($paused ? 'game_paused' : 'game_resumed', []);
        $this->dispatch('game-paused', paused: $paused);
    }

    public function onPhaseChanged()
    {
        $this->state = $this->state->fresh();
        $this->refreshNightFeed();
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

    public function onPlayerEliminated($payload)
    {
        $this->addLogEntry('player_eliminated', [
            'nickname' => $payload['nickname'] ?? 'unknown',
            'cause_key' => $payload['cause_key'] ?? null,
        ]);
    }

    public function onNightActionSubmitted()
    {
        $this->refreshNightFeed();
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
        $this->addLogEntry('game_finished', [
            'winning_faction' => $payload['winning_faction'] ?? 'unknown',
        ]);
    }

    private function guardNarrator(): void
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || !$requestPlayer->is_narrator || $requestPlayer->room_id !== $this->room->id) {
            event(new SuspiciousAccessAttempt($requestPlayer ?? $this->player, 'Non-narrator attempted narrator action'));
            session()->flash('error', __('errors.access_denied'));
            $this->redirect(route('home'));
        }
    }

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    }

    public function render()
    {
        $players = Player::where('room_id', $this->room->id)
            ->where('is_narrator', false)
            ->with('role')
            ->orderBy('created_at')
            ->get();

        $phase = $this->state->phase;
        $totalAlive = $players->where('is_alive', true)->count();

        $voteTally = [];
        $voteVoters = [];
        $voteCount = 0;
        if ($phase === 'voting') {
            $votes = Vote::where('game_state_id', $this->state->id)->with('voter')->get();
            $voteCount = $votes->count();
            foreach ($votes as $v) {
                $voteTally[$v->target_id] = ($voteTally[$v->target_id] ?? 0) + 1;
                $voteVoters[$v->target_id][] = $v->voter?->nickname ?? __('ui.game.unknown');
            }
            arsort($voteTally);
        }

        $coupleBonds = CoupleBond::where('game_state_id', $this->state->id)->get();
        $loverMap = [];
        foreach ($coupleBonds as $bond) {
            $loverMap[$bond->player_id] = $bond->partner_id;
            $loverMap[$bond->partner_id] = $bond->player_id;
        }

        $enchantedIds = $this->state->data['enchanted_player_ids'] ?? [];
        $data = $this->state->data ?? [];
        $paused = !empty($data['paused']);
        $isSequential = ($data['night_mode'] ?? 'parallel') === 'sequential';
        $activeNightRole = $data['active_night_role'] ?? null;
        $nightRoleOrderIndex = $data['night_role_order_index'] ?? -1;
        $nightRoleOrder = $data['night_role_order'] ?? [];

        $timerRemaining = null;
        $timerExpired = false;
        if (!in_array($phase, ['waiting', 'finished'], true)) {
            $startedAt = $data["{$phase}_timer_started_at"] ?? null;
            $configSeconds = $data["{$phase}_timer_config"] ?? 0;
            $timerExpired = !empty($data["{$phase}_timer_expired"]);
            if ($startedAt && $configSeconds > 0) {
                $elapsed = now()->diffInSeconds(\Carbon\Carbon::parse($startedAt));
                $timerRemaining = max(0, $configSeconds - $elapsed);
            }
        }

        $progressTracker = app(ProgressTracker::class);
        $readyStatuses = $progressTracker->getReadyStatuses($this->state);
        $readyCount = $progressTracker->getReadyCount($this->state);
        $totalActivePlayers = $progressTracker->getTotalActivePlayers($this->state, $phase);
        $nightProgress = $phase === 'night' ? $progressTracker->getNightActionProgress($this->state) : [];

        $littleGirlAlive = $players->contains(function ($p) {
            return $p->is_alive && $p->role && $p->role->key === 'little_girl';
        });

        $pendingRoleKeys = array_unique(array_column($this->pendingRoles, 'role_key'));
        $completedRoleKeys = array_unique(array_column($this->nightActionFeed, 'role_key'));

        $nightOrder = [
            'cupid', 'wolf_hound', 'werewolf', 'big_bad_wolf',
            'accursed_wolf_father', 'white_werewolf', 'bodyguard',
            'little_girl', 'seer', 'witch', 'pied_piper', 'fox', 'bear_tamer',
        ];

        $actionHistory = $data['action_history'] ?? [];
        $bearTamerGrowl = $this->checkBearTamerGrowl($players);

        return view('livewire.narrator.narrator-dashboard', [
            'players' => $players,
            'totalAlive' => $totalAlive,
            'phase' => $phase,
            'voteTally' => $voteTally,
            'voteVoters' => $voteVoters,
            'voteCount' => $voteCount,
            'loverMap' => $loverMap,
            'enchantedIds' => $enchantedIds,
            'paused' => $paused,
            'isSequential' => $isSequential,
            'activeNightRole' => $activeNightRole,
            'nightRoleOrderIndex' => $nightRoleOrderIndex,
            'nightRoleOrder' => $nightRoleOrder,
            'timerRemaining' => $timerRemaining,
            'timerExpired' => $timerExpired,
            'readyStatuses' => $readyStatuses,
            'readyCount' => $readyCount,
            'totalActivePlayers' => $totalActivePlayers,
            'nightProgress' => $nightProgress,
            'littleGirlAlive' => $littleGirlAlive,
            'pendingRoleKeys' => $pendingRoleKeys,
            'completedRoleKeys' => $completedRoleKeys,
            'nightOrder' => $nightOrder,
            'state' => $this->state,
            'actionHistory' => $actionHistory,
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
}
