<?php

namespace App\Game\Services;

use App\Events\GamePaused;
use App\Game\Engine\GameEngine;
use App\Models\GameState;
use App\Models\Player;

class NarratorControlService
{
    public function __construct(
        private GameEngine $engine,
    ) {}

    public function advancePhase(GameState $state, string $toPhase): void
    {
        $this->checkPaused($state);
        $this->clearTimer($state);
        $this->clearReadyStatuses($state);
        $this->engine->advancePhase($state, $toPhase);
    }

    public function resolveNight(GameState $state): void
    {
        $this->checkPaused($state);
        $this->clearTimer($state);
        $this->clearReadyStatuses($state);
        $this->engine->resolveNight($state);
    }

    public function resolveNightOnly(GameState $state): void
    {
        $this->checkPaused($state);
        $this->clearTimer($state);
        $this->clearReadyStatuses($state);
        $this->engine->resolveNightOnly($state);
    }

    public function resolveVote(GameState $state): void
    {
        $this->checkPaused($state);
        $this->clearTimer($state);
        $this->clearReadyStatuses($state);
        $this->engine->resolveVote($state);
    }

    public function resolveVoteOnly(GameState $state): void
    {
        $this->checkPaused($state);
        $this->clearTimer($state);
        $this->clearReadyStatuses($state);
        $this->engine->resolveVoteOnly($state);
    }

    public function togglePause(GameState $state): bool
    {
        $data = $state->data ?? [];

        $paused = !($data['paused'] ?? false);
        $data['paused'] = $paused;

        if ($paused) {
            $data['paused_at'] = now()->toIso8601String();
            $data['paused_phase'] = $state->phase;
            $data['paused_timer_remaining'] = $this->getTimerRemaining($state);
        } else {
            unset($data['paused_at']);
        }

        $state->data = $data;
        $state->save();

        event(new GamePaused($state->room, $paused));

        return $paused;
    }

    public function skipPlayerNightAction(Player $player, GameState $state): void
    {
        $data = $state->data ?? [];
        $skipped = $data['skipped_players'] ?? [];
        if (!in_array($player->id, $skipped)) {
            $skipped[] = $player->id;
        }
        $data['skipped_players'] = $skipped;
        $state->data = $data;
        $state->save();
    }

    public function forceEndNight(GameState $state): void
    {
        $this->checkPaused($state);
        $this->clearTimer($state);
        $this->clearReadyStatuses($state);
        $this->engine->resolveNightOnly($state);
    }

    public function setNightMode(GameState $state, string $mode): void
    {
        if (!in_array($mode, ['sequential', 'parallel'], true)) return;

        $data = $state->data ?? [];
        $data['night_mode'] = $mode;

        if ($mode === 'sequential') {
            $data['active_night_role'] = null;
            $data['night_role_order_index'] = -1;
            $data['night_role_order'] = $this->getDefaultNightOrder($state);
        } else {
            $data['active_night_role'] = null;
            $data['night_role_order_index'] = null;
            $data['night_role_order'] = [];
        }

        $state->data = $data;
        $state->save();
    }

    public function activateNextRole(GameState $state): ?string
    {
        $data = $state->data ?? [];
        $order = $data['night_role_order'] ?? [];
        $currentIndex = $data['night_role_order_index'] ?? -1;

        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= count($order)) {
            return null;
        }

        $nextRole = $order[$nextIndex];
        $data['night_role_order_index'] = $nextIndex;
        $data['active_night_role'] = $nextRole;
        $state->data = $data;
        $state->save();

        return $nextRole;
    }

    public function skipCurrentNightRole(GameState $state): ?string
    {
        $data = $state->data ?? [];
        $order = $data['night_role_order'] ?? [];
        $currentIndex = $data['night_role_order_index'] ?? -1;

        $role = $data['active_night_role'] ?? null;

        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= count($order)) {
            $data['active_night_role'] = null;
            $state->data = $data;
            $state->save();
            return null;
        }

        $nextRole = $order[$nextIndex];
        $data['night_role_order_index'] = $nextIndex;
        $data['active_night_role'] = $nextRole;
        $state->data = $data;
        $state->save();

        return $nextRole;
    }

    public function resetNightRolesProgress(GameState $state): void
    {
        $data = $state->data ?? [];
        $data['night_role_order_index'] = -1;
        $data['active_night_role'] = null;
        if (($data['night_mode'] ?? 'parallel') === 'sequential') {
            $data['night_role_order'] = $this->getDefaultNightOrder($state);
        }
        $state->data = $data;
        $state->save();
    }

    public function startTimer(GameState $state, string $phaseType, int $seconds): void
    {
        $data = $state->data ?? [];
        $timerKey = "{$phaseType}_timer_started_at";
        $configKey = "{$phaseType}_timer_config";
        $expiredKey = "{$phaseType}_timer_expired";

        $data[$configKey] = max(0, $seconds);
        $data[$timerKey] = $seconds > 0 ? now()->toIso8601String() : null;
        $data[$expiredKey] = false;
        $state->data = $data;
        $state->save();
    }

    public function extendTimer(GameState $state, int $extraSeconds): void
    {
        $phase = $state->phase;
        if ($phase === 'waiting' || $phase === 'finished') return;

        $data = $state->data ?? [];
        $timerKey = "{$phase}_timer_started_at";

        $data[$timerKey] = now()->toIso8601String();
        $data["{$phase}_timer_config"] = ($data["{$phase}_timer_config"] ?? 0) + $extraSeconds;
        $data["{$phase}_timer_expired"] = false;
        $state->data = $data;
        $state->save();
    }

    public function dismissTimer(GameState $state): void
    {
        $this->clearTimer($state);
    }

    public function clearReadyStatuses(GameState $state): void
    {
        $data = $state->data ?? [];
        $data['players_ready'] = [];
        $state->data = $data;
        $state->save();
    }

    private function clearTimer(GameState $state): void
    {
        $data = $state->data ?? [];
        foreach (['night', 'day', 'voting'] as $p) {
            $data["{$p}_timer_started_at"] = null;
            $data["{$p}_timer_expired"] = false;
        }
        $state->data = $data;
        $state->save();
    }

    private function getTimerRemaining(GameState $state): ?int
    {
        $phase = $state->phase;
        $data = $state->data ?? [];
        $timerKey = "{$phase}_timer_started_at";
        $configKey = "{$phase}_timer_config";

        $startedAt = $data[$timerKey] ?? null;
        $configSeconds = $data[$configKey] ?? 0;

        if (!$startedAt || $configSeconds <= 0) return null;

        $elapsed = now()->diffInSeconds(\Carbon\Carbon::parse($startedAt));
        return max(0, $configSeconds - $elapsed);
    }

    private function getDefaultNightOrder(GameState $state): array
    {
        $roomPlayerRoleIds = \App\Models\Player::where('room_id', $state->room_id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->whereNotNull('role_id')
            ->pluck('role_id')
            ->unique()
            ->toArray();

        $roles = \App\Models\Role::whereNotNull('night_order')
            ->whereIn('id', $roomPlayerRoleIds)
            ->orderBy('night_order')
            ->pluck('key')
            ->toArray();

        // Always include werewolf so the narrator wakes them
        if (!in_array('werewolf', $roles)) {
            $wolfOrder = \App\Models\Role::where('key', 'werewolf')->value('night_order');
            $roles = array_merge(['werewolf'], $roles);
            sort($roles);
        }

        return $roles;
    }

    private function checkPaused(GameState $state): void
    {
        $data = $state->data ?? [];
        if (!empty($data['paused'])) {
            throw new \RuntimeException(__('ui.narrator.game_paused_error'));
        }
    }
}
