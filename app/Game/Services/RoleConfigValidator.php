<?php

namespace App\Game\Services;

use App\Models\Role;

class RoleConfigValidator
{
    private array $uniqueRoles = [
        'angel', 'pied_piper',
        'bear_tamer', 'bodyguard', 'cupid', 'devoted_servant', 'elder', 'fox',
        'hunter', 'knight_with_rusty_sword', 'little_girl', 'scapegoat', 'seer',
        'stuttering_judge', 'village_idiot', 'witch',
        'accursed_wolf_father', 'big_bad_wolf', 'white_werewolf', 'wolf_hound',
    ];

    private array $werewolfFactionKeys = [
        'werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'wolf_hound',
    ];

    private array $specialGroupRoles = [
        'two_sisters' => 2,
        'three_brothers' => 3,
    ];

    public function validate(int $playerCount, array $roleCounts): array
    {
        $errors = [];

        $totalAssigned = array_sum($roleCounts);

        if ($playerCount < 4) {
            $errors[] = __('lobby.validation.min_players');
        }

        if ($totalAssigned !== $playerCount) {
            $errors[] = __('lobby.validation.role_count_mismatch');
        }

        $hasWerewolf = false;
        $hasVillage = false;

        foreach ($roleCounts as $roleKey => $count) {
            if ($count <= 0) continue;

            $role = Role::where('key', $roleKey)->first();
            if (!$role) continue;

            if ($role->faction === 'werewolves') {
                $hasWerewolf = true;
            }
            if ($role->faction === 'village') {
                $hasVillage = true;
            }

            if (in_array($roleKey, $this->uniqueRoles) && $count > 1) {
                $errors[] = __('lobby.validation.unique_role_max', ['role' => __("roles.{$roleKey}.name")]);
            }

            if (isset($this->specialGroupRoles[$roleKey])) {
                $expected = $this->specialGroupRoles[$roleKey];
                if ($count !== 0 && $count !== $expected) {
                    $errors[] = $roleKey === 'two_sisters'
                        ? __('lobby.validation.two_sisters_exact')
                        : __('lobby.validation.three_brothers_exact');
                }
            }
        }

        if (!$hasWerewolf) {
            $errors[] = __('lobby.validation.need_werewolf');
        }
        if (!$hasVillage) {
            $errors[] = __('lobby.validation.need_village');
        }

        return $errors;
    }

    public function getPerRoleErrors(array $roleCounts): array
    {
        $perRole = [];

        foreach ($roleCounts as $roleKey => $count) {
            $roleErrors = [];

            if (in_array($roleKey, $this->uniqueRoles) && $count > 1) {
                $roleErrors[] = __('lobby.validation.unique_role_max', ['role' => __("roles.{$roleKey}.name")]);
            }

            if (isset($this->specialGroupRoles[$roleKey]) && $count !== 0) {
                $expected = $this->specialGroupRoles[$roleKey];
                if ($count !== $expected) {
                    $roleErrors[] = $roleKey === 'two_sisters'
                        ? __('lobby.validation.two_sisters_exact')
                        : __('lobby.validation.three_brothers_exact');
                }
            }

            if (!empty($roleErrors)) {
                $perRole[$roleKey] = $roleErrors;
            }
        }

        return $perRole;
    }

    public function getMaxForRole(string $roleKey): int
    {
        if (in_array($roleKey, $this->uniqueRoles)) {
            return 1;
        }
        if (isset($this->specialGroupRoles[$roleKey])) {
            return $this->specialGroupRoles[$roleKey];
        }
        return 999;
    }

    public function isRoleAtMax(string $roleKey, int $currentCount, int $playerCount, array $allCounts): bool
    {
        $roleMax = $this->getMaxForRole($roleKey);
        if ($currentCount >= $roleMax) return true;

        if ($playerCount > 0) {
            $totalAssigned = array_sum($allCounts);
            if ($totalAssigned >= $playerCount) return true;
        }

        return false;
    }

    public function getWarnings(int $playerCount, array $roleCounts): array
    {
        $warnings = [];

        if ($playerCount < 4) return $warnings;

        $werewolfCount = $this->getWerewolfCount($roleCounts);
        $recommended = $this->getRecommendedWerewolfCount($playerCount);

        if ($werewolfCount > $recommended) {
            $warnings[] = __('lobby.validation.werewolf_recommendation', [
                'recommended' => $recommended,
                'actual' => $werewolfCount,
            ]);
        }

        return $warnings;
    }

    public function getBalanceStatus(int $playerCount, array $roleCounts): string
    {
        if ($playerCount < 4) return 'unbalanced';

        $werewolfCount = $this->getWerewolfCount($roleCounts);
        $recommended = $this->getRecommendedWerewolfCount($playerCount);

        $villagerCount = $roleCounts['villager'] ?? 0;
        $villageSpecialCount = 0;

        foreach ($roleCounts as $roleKey => $count) {
            if ($count <= 0 || $roleKey === 'villager') continue;
            $role = Role::where('key', $roleKey)->first();
            if ($role && $role->faction === 'village') {
                $villageSpecialCount += $count;
            }
        }

        $diff = $werewolfCount - $recommended;

        if ($diff >= 2) return 'werewolf_favored';
        if ($diff <= -2) return 'village_favored';

        if ($diff === 1) return 'slightly_werewolf_favored';
        if ($diff === -1) return 'slightly_village_favored';

        if ($villageSpecialCount >= 2) return 'balanced';
        if ($villageSpecialCount === 1) return 'slightly_village_favored';

        return 'unbalanced';
    }

    public function getWerewolfCount(array $roleCounts): int
    {
        $total = 0;
        foreach ($this->werewolfFactionKeys as $key) {
            $total += $roleCounts[$key] ?? 0;
        }
        return $total;
    }

    public function getVillageSpecialCount(array $roleCounts): int
    {
        $count = 0;
        foreach ($roleCounts as $roleKey => $c) {
            if ($c <= 0 || $roleKey === 'villager') continue;
            $role = Role::where('key', $roleKey)->first();
            if ($role && $role->faction === 'village') {
                $count += $c;
            }
        }
        return $count;
    }

    public function getRecommendedWerewolfCount(int $playerCount): int
    {
        return match (true) {
            $playerCount <= 6 => 1,
            $playerCount <= 9 => 2,
            $playerCount <= 12 => 3,
            $playerCount <= 15 => 4,
            $playerCount <= 18 => 5,
            default => 6,
        };
    }

    public function getUniqueRoles(): array
    {
        return $this->uniqueRoles;
    }

    public function getWerewolfFactionKeys(): array
    {
        return $this->werewolfFactionKeys;
    }

    public function getSpecialGroupRoles(): array
    {
        return $this->specialGroupRoles;
    }

    public static function getPresets(): array
    {
        return [
            4 => [
                'werewolf' => 1,
                'seer' => 1,
                'villager' => 2,
            ],
            6 => [
                'werewolf' => 1,
                'seer' => 1,
                'witch' => 1,
                'villager' => 3,
            ],
            8 => [
                'werewolf' => 2,
                'seer' => 1,
                'witch' => 1,
                'hunter' => 1,
                'villager' => 3,
            ],
            10 => [
                'werewolf' => 2,
                'seer' => 1,
                'witch' => 1,
                'hunter' => 1,
                'bodyguard' => 1,
                'villager' => 4,
            ],
            12 => [
                'werewolf' => 3,
                'seer' => 1,
                'witch' => 1,
                'hunter' => 1,
                'bodyguard' => 1,
                'villager' => 4,
            ],
            16 => [
                'werewolf' => 4,
                'seer' => 1,
                'witch' => 1,
                'hunter' => 1,
                'bodyguard' => 1,
                'fox' => 1,
                'villager' => 7,
            ],
            20 => [
                'werewolf' => 5,
                'seer' => 1,
                'witch' => 1,
                'hunter' => 1,
                'bodyguard' => 1,
                'fox' => 1,
                'cupid' => 1,
                'villager' => 9,
            ],
        ];
    }
}
