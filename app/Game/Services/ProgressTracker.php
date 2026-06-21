<?php

namespace App\Game\Services;

use App\Models\GameState;
use App\Models\Player;
use App\Models\NightAction;

class ProgressTracker
{
    public function markReady(Player $player, GameState $state, string $phase): void
    {
        $data = $state->data ?? [];
        $ready = $data['players_ready'] ?? [];

        if (!in_array($player->id, $ready)) {
            $ready[] = $player->id;
        }

        $data['players_ready'] = $ready;
        $state->data = $data;
        $state->save();
    }

    public function markNotReady(Player $player, GameState $state): void
    {
        $data = $state->data ?? [];
        $ready = $data['players_ready'] ?? [];

        $data['players_ready'] = array_values(array_diff($ready, [$player->id]));
        $state->data = $data;
        $state->save();
    }

    public function isReady(Player $player, GameState $state): bool
    {
        $data = $state->data ?? [];
        $ready = $data['players_ready'] ?? [];
        return in_array($player->id, $ready);
    }

    public function getReadyStatuses(GameState $state): array
    {
        $data = $state->data ?? [];
        $readyIds = $data['players_ready'] ?? [];

        $players = Player::where('room_id', $state->room_id)
            ->where('is_narrator', false)
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($players as $id => $player) {
            $result[$id] = [
                'is_ready' => in_array($id, $readyIds),
                'is_alive' => $player->is_alive,
                'nickname' => $player->nickname,
            ];
        }

        return $result;
    }

    public function getReadyCount(GameState $state): int
    {
        $data = $state->data ?? [];
        return count($data['players_ready'] ?? []);
    }

    public function getTotalActivePlayers(GameState $state, string $phase): int
    {
        $query = Player::where('room_id', $state->room_id)
            ->where('is_narrator', false)
            ->where('is_alive', true);

        if ($phase === 'voting') {
            $query->where('voting_banned', false);
        }

        return $query->count();
    }

    public function getNightActionProgress(GameState $state): array
    {
        $actions = NightAction::where('game_state_id', $state->id)
            ->whereNull('resolved_at')
            ->with('player.role')
            ->get();

        $submittedByRole = [];
        $submittedPlayerIds = [];

        foreach ($actions as $action) {
            $roleKey = $action->player?->role?->key;
            $playerId = $action->player_id;
            if ($roleKey) {
                $submittedByRole[$roleKey] = ($submittedByRole[$roleKey] ?? 0) + 1;
            }
            $submittedPlayerIds[] = $playerId;
        }

        $rolesWithActions = [
            'cupid', 'wolf_hound', 'werewolf', 'big_bad_wolf', 'accursed_wolf_father',
            'white_werewolf', 'bodyguard', 'seer', 'witch', 'pied_piper', 'fox',
        ];

        $alivePlayers = Player::where('room_id', $state->room_id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->with('role')
            ->get();

        $roleProgress = [];
        foreach ($rolesWithActions as $roleKey) {
            $playersWithRole = $alivePlayers->filter(fn ($p) => $p->role && $p->role->key === $roleKey);
            if ($playersWithRole->isEmpty()) continue;

            $total = $playersWithRole->count();
            $done = 0;
            $waitingPlayers = [];

            foreach ($playersWithRole as $p) {
                if (in_array($p->id, $submittedPlayerIds)) {
                    $done++;
                } else {
                    $waitingPlayers[] = [
                        'id' => $p->id,
                        'nickname' => $p->nickname,
                    ];
                }
            }

            $roleProgress[$roleKey] = [
                'total' => $total,
                'done' => $done,
                'waiting' => $total - $done,
                'waiting_players' => $waitingPlayers,
                'completed' => $done >= $total,
            ];
        }

        return $roleProgress;
    }

    public function getRoleSubmissionCount(GameState $state, string $roleKey): int
    {
        $playerIds = Player::where('room_id', $state->room_id)
            ->whereHas('role', fn ($q) => $q->where('key', $roleKey))
            ->pluck('id');

        return NightAction::where('game_state_id', $state->id)
            ->whereIn('player_id', $playerIds)
            ->whereNull('resolved_at')
            ->count();
    }

    public function isRoleActionComplete(GameState $state, string $roleKey): bool
    {
        $totalPlayers = Player::where('room_id', $state->room_id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->whereHas('role', fn ($q) => $q->where('key', $roleKey))
            ->count();

        if ($totalPlayers === 0) return true;

        $submittedCount = $this->getRoleSubmissionCount($state, $roleKey);

        return $submittedCount >= $totalPlayers;
    }

    public function clear(GameState $state): void
    {
        $data = $state->data ?? [];
        $data['players_ready'] = [];
        $state->data = $data;
        $state->save();
    }
}
