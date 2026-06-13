<?php

namespace App\Game\Actions\Werewolves;

use App\Game\Actions\BaseAction;
use App\Models\GameState;

class WolfHoundChooseSideAction extends BaseAction
{
    public function getPriority(): int { return 2; }

    public function isValid(GameState $state): bool
    {
        if (!parent::isValid($state)) return false;

        $data = $state->data ?? [];
        if (isset($data['wolf_hound_choice'])) return false;

        return $state->round === 1;
    }

    public function resolve(GameState $state): void
    {
        $data = $state->data ?? [];
        $choice = $this->record->metadata['side'] ?? 'villagers';
        $data['wolf_hound_choice'] = $choice;
        $state->data = $data;
        $state->save();
    }
}
