<div wire:poll.3s>
    <div class="space-y-1.5">
        @foreach($players as $p)
            <div class="flex items-center gap-3 px-3 py-2.5 bg-bg-surface/50 rounded-lg border border-border-default hover:border-accent-gold/30 transition-colors">
                <div class="w-2 h-2 rounded-full bg-accent-green shadow-lg shadow-accent-green/30"></div>
                <div class="w-7 h-7 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary">
                    {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                </div>
                <span class="text-text-primary text-sm font-medium">{{ $p['nickname'] }}</span>
            </div>
        @endforeach
    </div>

    @if(count($players) === 0)
        <div class="text-center py-12 space-y-3">
            <div class="text-3xl">👥</div>
            <p class="text-text-muted">{{ __('ui.lobby.no_players_yet') }}</p>
            <div class="flex justify-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-accent-gold animate-pulse animation-delay-200"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-accent-gold animate-pulse animation-delay-400"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-accent-gold animate-pulse animation-delay-600"></span>
            </div>
        </div>
    @endif
</div>
