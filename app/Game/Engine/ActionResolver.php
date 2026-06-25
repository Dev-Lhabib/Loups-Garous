<?php

namespace App\Game\Engine;

use App\Events\LoverDied;
use App\Events\NightResolved;
use App\Events\PlayerEliminated;
use App\Game\Actions\ActionInterface;
use App\Game\Actions\Neutral\PiedPiperEnchantAction;
use App\Game\Actions\Village\BodyguardProtectAction;
use App\Game\Actions\Village\CupidLinkAction;
use App\Game\Actions\Village\FoxInspectAction;
use App\Game\Actions\Village\SeerInspectAction;
use App\Game\Actions\Village\WitchPoisonAction;
use App\Game\Actions\Village\WitchSaveAction;
use App\Game\Actions\Werewolves\AccursedWolfFatherConvertAction;
use App\Game\Actions\Werewolves\BigBadWolfKillAction;
use App\Game\Actions\Werewolves\WerewolfKillAction;
use App\Game\Actions\Werewolves\WhiteWerewolfKillAction;
use App\Game\Actions\Werewolves\WolfHoundChooseSideAction;
use App\Models\CoupleBond;
use App\Models\GameState;
use App\Models\NightAction;
use App\Models\Player;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class ActionResolver
{
    private array $actionClassMap = [
        'kill' => WerewolfKillAction::class,
        'extra_kill' => BigBadWolfKillAction::class,
        'convert' => AccursedWolfFatherConvertAction::class,
        'solo_kill' => WhiteWerewolfKillAction::class,
        'choose_side' => WolfHoundChooseSideAction::class,
        'protect' => BodyguardProtectAction::class,
        'inspect' => SeerInspectAction::class,
        'save' => WitchSaveAction::class,
        'poison' => WitchPoisonAction::class,
        'enchant' => PiedPiperEnchantAction::class,
        'sniff' => FoxInspectAction::class,
        'link_lovers' => CupidLinkAction::class,
    ];

    public function __construct(
        private WinConditionChecker $winChecker,
    ) {}

    private function engine(): GameEngine
    {
        return app(GameEngine::class);
    }

    public function resolve(GameState $state): void
    {
        DB::transaction(function () use ($state) {
            $actions = NightAction::where('game_state_id', $state->id)
                ->whereNull('resolved_at')
                ->with(['player.role', 'target'])
                ->get();

            $actionInstances = [];
            foreach ($actions as $record) {
                $class = $this->actionClassMap[$record->action_type] ?? null;
                if (!$class) continue;
                $actionInstances[] = new $class($record);
            }

            usort($actionInstances, fn (ActionInterface $a, ActionInterface $b) => $a->getPriority() <=> $b->getPriority());

            // Step 1: Knight with Rusty Sword delayed death
            $this->applyKnightInfection($state);

            // Commit bodyguard protection first (priority 2 actions)
            foreach ($actionInstances as $action) {
                if ($action instanceof BodyguardProtectAction && $action->isValid($state)) {
                    $action->resolve($state);
                }
            }

            // Process all remaining actions in priority order
            $killTargetIds = [];
            $saveTargetId = null;
            $wolfFatherConverted = false;
            $bigBadWolfTargetId = null;
            $whiteWerewolfTargetId = null;
            $poisonTargetId = null;

            foreach ($actionInstances as $action) {
                if (!$action->isValid($state)) continue;

                if ($action instanceof WerewolfKillAction) {
                    $action->resolve($state);
                    $data = $state->data ?? [];
                    $killTargetIds[] = $data['werewolf_kill_target_id'] ?? null;
                } elseif ($action instanceof BigBadWolfKillAction) {
                    $action->resolve($state);
                    $data = $state->data ?? [];
                    $bigBadWolfTargetId = $data['big_bad_wolf_target_id'] ?? null;
                } elseif ($action instanceof AccursedWolfFatherConvertAction) {
                    $action->resolve($state);
                    $wolfFatherConverted = true;
                    $data = $state->data ?? [];
                    $convertTargetId = $data['wolf_father_convert_target_id'] ?? null;
                    if ($convertTargetId) {
                        $killTargetIds = array_filter($killTargetIds, fn ($id) => $id !== $convertTargetId);
                    }
                } elseif ($action instanceof WhiteWerewolfKillAction) {
                    $action->resolve($state);
                    $data = $state->data ?? [];
                    $whiteWerewolfTargetId = $data['white_werewolf_solo_target_id'] ?? null;
                } elseif ($action instanceof WitchSaveAction) {
                    $action->resolve($state);
                    $data = $state->data ?? [];
                    $saveTargetId = $data['witch_save_target_id'] ?? null;
                } elseif ($action instanceof WitchPoisonAction) {
                    $action->resolve($state);
                    $data = $state->data ?? [];
                    $poisonTargetId = $data['witch_poison_target_id'] ?? null;
                } elseif ($action instanceof SeerInspectAction) {
                    $action->resolve($state);
                } elseif ($action instanceof FoxInspectAction) {
                    $action->resolve($state);
                } elseif ($action instanceof PiedPiperEnchantAction) {
                    $action->resolve($state);
                    $winner = $this->winChecker->check($state);
                    if ($winner) {
                        $this->engine()->endGame($state, $winner);
                        return;
                    }
                } elseif ($action instanceof CupidLinkAction) {
                    $action->resolve($state);
                } elseif ($action instanceof WolfHoundChooseSideAction) {
                    $action->resolve($state);
                }
            }

            $data = $state->data ?? [];
            $protectedId = $data['bodyguard_protected_id'] ?? null;

            $killTargetIds = array_filter($killTargetIds);

            // Apply werewolf kill (cancel if protected or wolf-father converted)
            $deaths = [];
            if (!$wolfFatherConverted) {
                foreach ($killTargetIds as $targetId) {
                    if ($targetId && $targetId !== $protectedId) {
                        $deaths[] = [$targetId, 'eliminated_by_werewolf'];
                    }
                }
            }

            $existingIds = array_map(fn ($t) => $t[0], $deaths);

            // Apply Big Bad Wolf extra kill
            if ($bigBadWolfTargetId && !in_array($bigBadWolfTargetId, $existingIds)) {
                $anyWolfDead = Player::where('room_id', $state->room_id)
                    ->where('is_alive', false)
                    ->whereHas('role', fn ($q) => $q->whereIn('key', ['werewolf', 'big_bad_wolf', 'accursed_wolf_father']))
                    ->exists();
                if (!$anyWolfDead) {
                    $deaths[] = [$bigBadWolfTargetId, 'eliminated_by_big_bad_wolf'];
                    $existingIds[] = $bigBadWolfTargetId;
                }
            }

            // Apply White Werewolf solo kill
            if ($whiteWerewolfTargetId && !in_array($whiteWerewolfTargetId, $existingIds)) {
                $deaths[] = [$whiteWerewolfTargetId, 'eliminated_by_white_werewolf'];
                $existingIds[] = $whiteWerewolfTargetId;
            }

            // Apply Witch save — cancel werewolf kill on same target
            if ($saveTargetId) {
                $deaths = array_filter($deaths, fn ($t) => $t[0] !== $saveTargetId);
                $existingIds = array_map(fn ($t) => $t[0], $deaths);
            }

            // Apply Witch poison
            if ($poisonTargetId && !in_array($poisonTargetId, $existingIds)) {
                $deaths[] = [$poisonTargetId, 'eliminated_by_witch'];
            }

            // Mark all actions as resolved
            NightAction::where('game_state_id', $state->id)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);

            // Apply deaths with chain
            $eliminatedNicknames = [];
            $this->applyDeaths($state, $deaths, $eliminatedNicknames);

            if (($state->data['winning_faction'] ?? null) !== null) {
                return;
            }

            $eliminatedNicknames = array_filter(array_unique($eliminatedNicknames));

            $data = $state->data ?? [];
            $data['last_night_deaths'] = array_values($eliminatedNicknames);
            $state->data = $data;
            $state->save();

            event(new NightResolved($state, array_values($eliminatedNicknames)));
        });
    }

    private function applyKnightInfection(GameState $state): void
    {
        $data = $state->data ?? [];
        $infectedId = $data['infected_werewolf_id'] ?? null;

        if ($infectedId) {
            $infected = Player::find($infectedId);
            if ($infected && $infected->is_alive) {
                $infected->is_alive = false;
                $infected->save();

                event(new PlayerEliminated($infected, 'eliminated_by_infection'));

                $bond = CoupleBond::where('game_state_id', $state->id)
                    ->where(function ($q) use ($infectedId) {
                        $q->where('player_id', $infectedId)
                          ->orWhere('partner_id', $infectedId);
                    })->first();

                if ($bond) {
                    $partnerId = $bond->player_id === $infectedId ? $bond->partner_id : $bond->player_id;
                    $partner = Player::find($partnerId);
                    if ($partner && $partner->is_alive) {
                        event(new LoverDied($infected, $partner));
                        $partner->is_alive = false;
                        $partner->save();
                        event(new PlayerEliminated($partner, 'eliminated_by_lover'));
                    }
                }

                $winner = $this->winChecker->check($state);
                if ($winner) {
                    $this->engine()->endGame($state, $winner);
                }

                $data['infected_werewolf_id'] = null;
                $state->data = $data;
                $state->save();
            }
        }
    }

    private function applyDeaths(GameState $state, array $deathTuples, array &$eliminatedNicknames): void
    {
        $toProcess = $deathTuples;
        $processed = [];

        while (!empty($toProcess)) {
            $entry = array_shift($toProcess);
            if (is_array($entry)) {
                $playerId = $entry[0];
                $cause = $entry[1] ?? null;
            } else {
                $playerId = $entry;
                $cause = null;
            }

            if (in_array($playerId, $processed)) continue;
            $processed[] = $playerId;

            $player = Player::find($playerId);
            if (!$player || !$player->is_alive) continue;

            $role = $player->role;

            // Knight with Rusty Sword — mark werewolf killer before death
            if ($role && $role->key === 'knight_with_rusty_sword') {
                $data = $state->data ?? [];
                $data['knight_killed_by_werewolf'] = true;
                $data['infected_werewolf_id'] = null;
                $state->data = $data;
                $state->save();
            }

            // Hunter — if killed, apply pre-selected target
            if ($role && $role->key === 'hunter') {
                $data = $state->data ?? [];
                $hunterTargetId = $data['hunter_pre_target_id'] ?? null;
                if ($hunterTargetId && $hunterTargetId !== $playerId) {
                    $hunterTarget = Player::find($hunterTargetId);
                    if ($hunterTarget && $hunterTarget->is_alive && !in_array($hunterTargetId, $processed)) {
                        $data['hunter_shot_used'] = true;
                        $state->data = $data;
                        $state->save();
                        $toProcess[] = [$hunterTargetId, 'eliminated_by_hunter'];
                    }
                }
            }

            $eliminatedNicknames[] = $player->nickname;
            $player->is_alive = false;
            $player->save();

            event(new PlayerEliminated($player, $cause));

            $winner = $this->winChecker->check($state);
            if ($winner) {
                $this->engine()->endGame($state, $winner);
                return;
            }

            // Lover death chain — partner dies immediately after
            $bond = CoupleBond::where('game_state_id', $state->id)
                ->where(function ($q) use ($playerId) {
                    $q->where('player_id', $playerId)
                      ->orWhere('partner_id', $playerId);
                })
                ->first();

            if ($bond) {
                $partnerId = $bond->player_id === $playerId ? $bond->partner_id : $bond->player_id;
                $partner = Player::find($partnerId);

                if ($partner && $partner->is_alive) {
                    event(new LoverDied($player, $partner));

                    if (!in_array($partnerId, $processed)) {
                        $toProcess[] = [$partnerId, 'eliminated_by_lover'];
                    }
                }
            }
        }
    }
}
