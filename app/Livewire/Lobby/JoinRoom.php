<?php

namespace App\Livewire\Lobby;

use App\Game\Services\LobbyService;
use App\Models\Room;
use Livewire\Component;

class JoinRoom extends Component
{
    public string $code = '';
    public string $nickname = '';
    public bool $hasCode = false;
    public ?Room $detectedRoom = null;

    public function mount($code = null)
    {
        $code = $code ?: request()->query('code');
        if ($code) {
            $this->code = strtoupper(substr($code, 0, 6));
            $this->hasCode = true;
            $this->detectedRoom = Room::where('code', $this->code)->where('status', 'waiting')->first();
        }
    }

    public function submit(LobbyService $lobbyService)
    {
        $this->validate([
            'code' => 'required|string|size:6',
            'nickname' => 'required|string|max:30',
        ]);

        $room = Room::where('code', strtoupper($this->code))->firstOrFail();
        $player = $lobbyService->joinRoom($room, $this->nickname, request());
        $this->redirect(route('lobby.player', $room->code));
    }

    public function render()
    {
        return view('livewire.lobby.join-room')
            ->layout('layouts.app');
    }
}
