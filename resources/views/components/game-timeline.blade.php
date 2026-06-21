@props([
    'entries' => [],
])

@php
$eventColors = [
    'phase_changed' => ['dot' => 'bg-accent-gold', 'border' => 'border-accent-gold/30', 'text' => 'text-accent-gold'],
    'player_eliminated' => ['dot' => 'bg-accent-red', 'border' => 'border-accent-red/30', 'text' => 'text-accent-red'],
    'night_resolved' => ['dot' => 'bg-accent-blue', 'border' => 'border-accent-blue/30', 'text' => 'text-accent-blue'],
    'vote_submitted' => ['dot' => 'bg-text-muted', 'border' => 'border-border-default', 'text' => 'text-text-secondary'],
    'voting_resolved' => ['dot' => 'bg-accent-red', 'border' => 'border-accent-red/30', 'text' => 'text-accent-red'],
    'suspicious_access' => ['dot' => 'bg-accent-red-dark', 'border' => 'border-accent-red/30', 'text' => 'text-accent-red'],
    'game_started' => ['dot' => 'bg-accent-gold', 'border' => 'border-accent-gold/30', 'text' => 'text-accent-gold'],
    'game_finished' => ['dot' => 'bg-accent-gold', 'border' => 'border-accent-gold/30', 'text' => 'text-accent-gold'],
    'all_players_ready' => ['dot' => 'bg-accent-green', 'border' => 'border-accent-green/30', 'text' => 'text-accent-green'],
    'game_paused' => ['dot' => 'bg-accent-gold', 'border' => 'border-accent-gold/30', 'text' => 'text-accent-gold'],
    'game_resumed' => ['dot' => 'bg-accent-green', 'border' => 'border-accent-green/30', 'text' => 'text-accent-green'],
    'timer_started' => ['dot' => 'bg-accent-blue', 'border' => 'border-accent-blue/30', 'text' => 'text-accent-blue'],
    'timer_extended' => ['dot' => 'bg-accent-blue', 'border' => 'border-accent-blue/30', 'text' => 'text-accent-blue'],
    'timer_expired' => ['dot' => 'bg-accent-red', 'border' => 'border-accent-red/30', 'text' => 'text-accent-red'],
    'timer_dismissed' => ['dot' => 'bg-text-muted', 'border' => 'border-border-default', 'text' => 'text-text-secondary'],
    'night_role_activated' => ['dot' => 'bg-accent-blue', 'border' => 'border-accent-blue/30', 'text' => 'text-accent-blue'],
    'night_sequence_complete' => ['dot' => 'bg-accent-green', 'border' => 'border-accent-green/30', 'text' => 'text-accent-green'],
    'player_skipped' => ['dot' => 'bg-accent-red-dark', 'border' => 'border-accent-red/30', 'text' => 'text-accent-red'],
];
@endphp

<div class="glass-panel border border-border-default p-4">
    <h3 class="text-text-primary text-sm font-semibold mb-3 flex items-center gap-2">
        <span>📜</span>
        <span>{{ __('ui.narrator.game_log') }}</span>
        <span class="text-xs text-text-muted ms-auto font-mono">{{ count($entries) }}</span>
    </h3>
    <div class="space-y-0 max-h-80 overflow-y-auto scrollbar-thin">
        @forelse($entries as $entry)
            @php
                $type = $entry['type'] ?? 'unknown';
                $colors = $eventColors[$type] ?? ['dot' => 'bg-text-muted', 'border' => 'border-border-default', 'text' => 'text-text-secondary'];
                $ts = isset($entry['timestamp']) ? \Carbon\Carbon::parse($entry['timestamp'])->isoFormat('HH:mm') : '';
            @endphp
            <div class="relative ps-7 pb-2.5 group hover:bg-bg-elevated/30 rounded-sm transition-colors">
                <div class="absolute start-2.5 top-1.5 w-2 h-2 rounded-full {{ $colors['dot'] }} ring-2 ring-bg-card"></div>
                @if(!$loop->last)
                    <div class="absolute start-3 top-4 bottom-0 w-px bg-border-default"></div>
                @endif
                <div class="flex items-start gap-2">
                    <span class="text-[10px] text-text-muted flex-shrink-0 mt-0.5 font-mono">{{ $ts }}</span>
                    <div class="text-xs {{ $colors['text'] }}">
                        @if($type === 'phase_changed')
                            [{{ __("ui.phase.{$entry['from']}") }} → {{ __("ui.phase.{$entry['to']}") }}]
                        @elseif($type === 'player_eliminated')
                            {{ __('ui.narrator.log_eliminated', ['name' => $entry['nickname'] ?? '?']) }}
                            @if(!empty($entry['cause_key']))
                                <span class="text-text-muted ml-1">· {{ __("game.{$entry['cause_key']}") }}</span>
                            @endif
                        @elseif($type === 'night_resolved')
                            {{ __('ui.narrator.log_night_resolved') }}
                        @elseif($type === 'vote_submitted')
                            {{ __('ui.narrator.log_vote_cast') }}
                        @elseif($type === 'voting_resolved')
                            {{ __('ui.narrator.log_voting_resolved') }}
                        @elseif($type === 'suspicious_access')
                            {{ __('ui.narrator.log_suspicious', ['nickname' => $entry['player_nickname'] ?? '?', 'details' => $entry['details'] ?? '']) }}
                        @elseif($type === 'game_started')
                            {{ __('ui.narrator.log_game_started') }}
                        @elseif($type === 'game_finished')
                            {{ __('ui.narrator.log_game_finished', ['faction' => __("ui.win.{$entry['winning_faction']}")]) }}
                        @elseif($type === 'all_players_ready')
                            {{ __('ui.narrator.log_all_players_ready') }}
                        @elseif($type === 'game_paused')
                            {{ __('ui.narrator.log_game_paused') }}
                        @elseif($type === 'game_resumed')
                            {{ __('ui.narrator.log_game_resumed') }}
                        @elseif($type === 'timer_started')
                            {{ __('ui.narrator.log_timer_started', ['seconds' => $entry['seconds'] ?? '']) }}
                        @elseif($type === 'timer_extended')
                            {{ __('ui.narrator.log_timer_extended', ['seconds' => $entry['seconds'] ?? '']) }}
                        @elseif($type === 'timer_expired')
                            {{ __('ui.narrator.log_timer_expired') }}
                        @elseif($type === 'timer_dismissed')
                            {{ __('ui.narrator.log_timer_dismissed') }}
                        @elseif($type === 'night_role_activated')
                            {{ __('ui.narrator.log_night_role_activated', ['role' => __("roles.{$entry['role']}.name")]) }}
                        @elseif($type === 'night_sequence_complete')
                            {{ __('ui.narrator.log_night_sequence_complete') }}
                        @elseif($type === 'player_skipped')
                            {{ __('ui.narrator.log_player_skipped', ['nickname' => $entry['nickname'] ?? '?']) }}
                        @else
                            {{ $entry['type'] ?? __('ui.narrator.log_empty') }}
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-text-muted text-xs text-center py-6 italic">{{ __('ui.narrator.log_empty') }}</p>
        @endforelse
    </div>
</div>
