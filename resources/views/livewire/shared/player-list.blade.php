<div wire:poll.3s>
    <div class="space-y-1">
        @foreach($players as $p)
            <div class="flex items-center gap-2.5 md:gap-3 px-2.5 md:px-3 py-2 md:py-2.5 bg-bg-surface/50 rounded-lg border border-border-default hover:border-accent-gold/30 transition-colors">
                <div class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-accent-green shadow-lg shadow-accent-green/30"></div>
                <div class="w-6 h-6 md:w-7 md:h-7 rounded-full bg-bg-elevated flex items-center justify-center text-[10px] md:text-xs font-bold text-text-secondary">
                    {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                </div>
                <span class="text-text-primary text-xs md:text-sm font-medium">{{ $p['nickname'] }}</span>
            </div>
        @endforeach
    </div>

    @if(count($players) === 0)
        <div class="text-center py-8 md:py-12 space-y-2 md:space-y-3">
            <div class="text-2xl md:text-3xl">👥</div>
            <p class="text-text-muted text-sm md:text-base">{{ __('ui.lobby.no_players_yet') }}</p>
            <div class="flex justify-center gap-1">
                <span class="w-1 h-1 md:w-1.5 md:h-1.5 rounded-full bg-accent-gold animate-pulse animation-delay-200"></span>
                <span class="w-1 h-1 md:w-1.5 md:h-1.5 rounded-full bg-accent-gold animate-pulse animation-delay-400"></span>
                <span class="w-1 h-1 md:w-1.5 md:h-1.5 rounded-full bg-accent-gold animate-pulse animation-delay-600"></span>
            </div>
        </div>
    @endif
</div>
