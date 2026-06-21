<?php

namespace App\Livewire\Player;

use App\Game\Services\VotingService;
use App\Models\Player;
use App\Models\Room;
use App\Models\Vote;
use Livewire\Component;

class VotingPanel extends Component
{
    public Room $room;
    public Player $player;
    public ?string $selectedTargetId = null;
    public bool $submitted = false;
    public bool $confirming = false;
    public array $alivePlayers = [];

    public function mount(Room $room, Player $player)
    {
        $this->room = $room;
        $this->player = $player;

        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $player->id) {
            return;
        }

        if (!$player->is_alive || $player->is_narrator) return;

        $state = $room->gameState;
        if (!$state || $state->phase !== 'voting') return;

        $this->alivePlayers = Player::where('room_id', $room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->where('id', '!=', $player->id)
            ->orderBy('nickname')
            ->get()
            ->toArray();

        $existing = Vote::where('game_state_id', $state->id)
            ->where('voter_id', $player->id)
            ->first();

        if ($existing) {
            $this->submitted = true;
        }
    }

    public function selectTarget(string $targetId)
    {
        $this->selectedTargetId = $targetId;
        $this->confirming = true;
    }

    public function confirmVote()
    {
        $requestPlayer = $this->resolvePlayerFromSession();
        if (!$requestPlayer || $requestPlayer->id !== $this->player->id) abort(403);

        $state = $this->room->gameState;
        if (!$state || $state->phase !== 'voting' || !$this->player->is_alive) return;

        if ($this->player->voting_banned) return;

        $target = Player::find($this->selectedTargetId);
        if (!$target) return;

        $service = app(VotingService::class);
        $result = $service->submitVote($this->player, $target, $state);

        if ($result) {
            $this->submitted = true;
            $this->confirming = false;
        }
    }

    public function cancelSelection()
    {
        $this->selectedTargetId = null;
        $this->confirming = false;
    }

    public function getListeners()
    {
        return [
            "echo-private:room.{$this->room->id},PhaseChanged" => '$refresh',
        ];
    }

    private function resolvePlayerFromSession(): ?Player
    {
        $token = request()->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    }

    public function render()
    {
        $banned = $this->player->voting_banned;
        $state = $this->room->gameState;

        if (!$this->player->is_alive || $this->player->is_narrator || ($state && $state->phase !== 'voting')) {
            return '<div></div>';
        }

        $totalVoters = \App\Models\Player::where('room_id', $this->room->id)
            ->where('is_alive', true)
            ->where('is_narrator', false)
            ->where('voting_banned', false)
            ->count();

        $voteCount = \App\Models\Vote::where('game_state_id', $state->id)->count();

        return view('livewire.player.voting-panel', [
            'banned' => $banned,
            'totalVoters' => $totalVoters,
            'voteCount' => $voteCount,
        ]);
    }
}
