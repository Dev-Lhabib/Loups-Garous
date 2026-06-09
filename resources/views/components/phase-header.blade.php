@props([
    'phase' => 'waiting',
    'round' => 1,
    'aliveCount' => 0,
    'totalCount' => 0,
    'roomCode' => '',
    'narratorView' => false,
])

@php
$phaseConfig = [
    'night' => [
        'icon' => '🌙',
        'label' => __('ui.phase.night'),
        'subtitle' => __('ui.game.waiting_night_action'),
        'gradient' => 'from-accent-blue/20 via-bg-surface to-bg-primary',
        'border' => 'border-accent-blue/30',
        'accent' => 'text-accent-blue',
        'bgAccent' => 'bg-accent-blue/10',
        'glow' => 'glow-blue',
        'bar' => 'bg-accent-blue',
    ],
    'day' => [
        'icon' => '☀️',
        'label' => __('ui.phase.day'),
        'subtitle' => __('ui.game.discussion_time'),
        'gradient' => 'from-accent-gold/10 via-bg-surface to-bg-primary',
        'border' => 'border-accent-gold/20',
        'accent' => 'text-accent-gold',
        'bgAccent' => 'bg-accent-gold/10',
        'glow' => 'glow-gold',
        'bar' => 'bg-accent-gold',
    ],
    'voting' => [
        'icon' => '🗳️',
        'label' => __('ui.phase.voting'),
        'subtitle' => __('ui.vote.title'),
        'gradient' => 'from-accent-red/15 via-bg-surface to-bg-primary',
        'border' => 'border-accent-red/30',
        'accent' => 'text-accent-red',
        'bgAccent' => 'bg-accent-red/10',
        'glow' => 'glow-red',
        'bar' => 'bg-accent-red',
    ],
    'finished' => [
        'icon' => '🏆',
        'label' => __('ui.phase.finished'),
        'subtitle' => '',
        'gradient' => 'from-accent-gold/20 via-bg-surface to-bg-primary',
        'border' => 'border-accent-gold/30',
        'accent' => 'text-accent-gold',
        'bgAccent' => 'bg-accent-gold/10',
        'glow' => 'glow-gold',
        'bar' => 'bg-accent-gold',
    ],
    'waiting' => [
        'icon' => '⏳',
        'label' => __('ui.phase.waiting'),
        'subtitle' => '',
        'gradient' => 'from-bg-elevated via-bg-surface to-bg-primary',
        'border' => 'border-border-default',
        'accent' => 'text-text-secondary',
        'bgAccent' => 'bg-bg-elevated/50',
        'glow' => '',
        'bar' => 'bg-border-default',
    ],
];

$cfg = $phaseConfig[$phase] ?? $phaseConfig['waiting'];
$pct = $totalCount > 0 ? round(($aliveCount / $totalCount) * 100) : 0;
@endphp

<div class="glass-panel border {{ $cfg['border'] }} overflow-hidden transition-all duration-300 {{ $cfg['glow'] }} {{ $narratorView ? '' : 'w-full max-w-2xl mx-auto' }}">
    <div class="bg-gradient-to-r {{ $cfg['gradient'] }} p-4 md:p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="text-3xl md:text-4xl flex-shrink-0 animate-heartbeat">{{ $cfg['icon'] }}</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="font-serif text-xl md:text-2xl font-bold text-text-primary truncate">
                            {{ $cfg['label'] }}
                        </h1>
                        @if($round > 0)
                            <span class="text-xs font-mono {{ $cfg['accent'] }} {{ $cfg['bgAccent'] }} px-2 py-0.5 rounded-full ring-1 ring-inset {{ $cfg['border'] }}">
                                R{{ $round }}
                            </span>
                        @endif
                    </div>
                    @if($cfg['subtitle'])
                        <p class="text-sm text-text-muted mt-0.5">{{ $cfg['subtitle'] }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4 flex-shrink-0">
                @if($aliveCount > 0)
                    <div class="text-right">
                        <p class="text-xs text-text-muted uppercase tracking-wider hidden sm:block">{{ __('ui.game.players_alive') }}</p>
                        <p class="text-lg font-bold font-mono {{ $cfg['accent'] }}">
                            {{ $aliveCount }}<span class="text-text-muted text-sm">/{{ $totalCount }}</span>
                        </p>
                    </div>
                @endif
                @if($roomCode)
                    <div class="hidden sm:block">
                        <p class="text-xs text-text-muted uppercase tracking-wider">{{ __('ui.lobby.room_code') }}</p>
                        <p class="text-sm font-mono tracking-wider text-accent-gold">{{ $roomCode }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($aliveCount > 0 && $totalCount > 0)
            <div class="mt-3 flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-bg-surface rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 ease-out {{ $cfg['bar'] }}"
                        style="width: {{ $pct }}%">
                    </div>
                </div>
                <span class="text-xs text-text-muted font-mono tabular-nums">{{ $pct }}%</span>
            </div>
        @endif
    </div>
</div>
