<?php

namespace App\Http\Controllers;

use App\Exceptions\GameActionException;
use App\Game\Services\VotingService;
use App\Models\Player;
use App\Models\Room;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function submit(Request $request, VotingService $service)
    {
        $player = $request->get('_player');
        if (!$player) {
            return response()->json(['error' => __('errors.session_expired')], 401);
        }

        $target = Player::findOrFail($request->input('target_id'));

        $state = $player->room->gameState;
        if (!$state) {
            return response()->json(['error' => __('errors.game_not_found')], 403);
        }

        try {
            $vote = $service->submitVote($player, $target, $state);

            return response()->json([
                'status' => $vote ? 'ok' : 'duplicate',
            ]);
        } catch (GameActionException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
