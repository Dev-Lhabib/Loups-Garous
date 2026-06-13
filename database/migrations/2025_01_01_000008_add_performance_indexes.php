<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_actions', function (Blueprint $table) {
            $table->index(['game_state_id', 'resolved_at']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->index(['game_state_id', 'voter_id']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->index(['room_id', 'is_alive']);
        });

        Schema::table('couple_bonds', function (Blueprint $table) {
            $table->index(['game_state_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::table('night_actions', function (Blueprint $table) {
            $table->dropIndex(['game_state_id', 'resolved_at']);
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropIndex(['game_state_id', 'voter_id']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex(['room_id', 'is_alive']);
        });

        Schema::table('couple_bonds', function (Blueprint $table) {
            $table->dropIndex(['game_state_id', 'player_id']);
        });
    }
};
