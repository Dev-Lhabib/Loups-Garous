<?php

namespace App\Http\Middleware;

use App\Models\Player;
use Closure;
use Illuminate\Http\Request;

class IdentifyPlayer
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('session_token');

        if (!$token) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('errors.session_expired')], 401);
            }
            return redirect(route('home'))->with('error', __('errors.session_expired'));
        }

        $player = Player::where('session_token', $token)->first();

        if (!$player) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('errors.session_expired')], 401);
            }
            return redirect(route('home'))->with('error', __('errors.session_expired'));
        }

        $request->merge(['_player' => $player]);

        return $next($request);
    }
}
