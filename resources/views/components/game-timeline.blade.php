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
];
@endphp

<div class="glass-panel border border-border-default p-4">
    <h3 class="text-text-primary text-sm font-semibold mb-3 flex items-center gap-2">
        <span>📜</span>
        <span>{{ __('ui.narrator.game_log') }}</span>
        <span class="text-xs text-text-muted ml-auto font-mono">{{ count($entries) }}</span>
    </h3>
    <div class="space-y-0 max-h-80 overflow-y-auto scrollbar-thin">
        @forelse($entries as $entry)
            @php
                $type = $entry['type'] ?? 'unknown';
                $colors = $eventColors[$type] ?? ['dot' => 'bg-text-muted', 'border' => 'border-border-default', 'text' => 'text-text-secondary'];
                $ts = isset($entry['timestamp']) ? \Carbon\Carbon::parse($entry['timestamp'])->isoFormat('HH:mm') : '';
            @endphp
            <div class="relative pl-7 pb-2.5 group hover:bg-bg-elevated/30 rounded-sm transition-colors">
                <div class="absolute left-2.5 top-1.5 w-2 h-2 rounded-full {{ $colors['dot'] }} ring-2 ring-bg-card"></div>
                @if(!$loop->last)
                    <div class="absolute left-3 top-4 bottom-0 w-px bg-border-default"></div>
                @endif
                <div class="flex items-start gap-2">
                    <span class="text-[10px] text-text-muted flex-shrink-0 mt-0.5 font-mono">{{ $ts }}</span>
                    <div class="text-xs {{ $colors['text'] }}">
                        @if($type === 'phase_changed')
                            [{{ __("ui.phase.{$entry['from']}") }} → {{ __("ui.phase.{$entry['to']}") }}]
                        @elseif($type === 'player_eliminated')
                            {{ __('ui.narrator.log_eliminated', ['name' => $entry['nickname'] ?? '?']) }}
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
