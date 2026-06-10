<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8 md:py-12">
    <div class="w-full max-w-sm mx-auto text-center space-y-6 md:space-y-8 animate-fadeInUp">

        {{-- Room Code --}}
        <div class="glass-panel border border-accent-gold/30 inline-block px-5 py-2.5 md:px-6 md:py-3">
            <p class="text-text-muted text-[10px] md:text-xs uppercase tracking-widest mb-1">{{ __('ui.lobby.room_code') }}</p>
            <p class="font-mono text-xl md:text-2xl tracking-[0.3em] text-accent-gold font-bold">{{ $room->code }}</p>
        </div>

        {{-- Player Identity --}}
        <div class="space-y-2 md:space-y-3">
            <div class="w-14 h-14 md:w-16 md:h-16 mx-auto rounded-full bg-gradient-to-br from-accent-gold/30 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center">
                <span class="text-xl md:text-2xl font-bold text-accent-gold">{{ strtoupper(substr($player->nickname, 0, 1)) }}</span>
            </div>
            <p class="text-text-muted text-xs md:text-sm">{{ __('ui.lobby.you_joined_as') }}</p>
            <p class="font-serif text-xl md:text-2xl text-accent-gold font-bold">{{ $player->nickname }}</p>
        </div>

        {{-- Players --}}
        <div>
            <h3 class="text-text-muted text-xs md:text-sm uppercase tracking-wider mb-3 md:mb-4 flex items-center justify-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-accent-gold"></span>
                <span>{{ __('ui.lobby.connected_players') }}</span>
            </h3>
            <livewire:shared.player-list :room="$room" :wire:key="'player-list-'.$room->id" />
        </div>

        {{-- Waiting State --}}
        <div class="space-y-3 md:space-y-4 animate-fadeInUp">
            <div class="flex items-center justify-center gap-2">
                <div class="w-2 h-2 md:w-2.5 md:h-2.5 bg-accent-gold rounded-full animate-pulse animation-delay-200"></div>
                <div class="w-2 h-2 md:w-2.5 md:h-2.5 bg-accent-gold rounded-full animate-pulse animation-delay-400"></div>
                <div class="w-2 h-2 md:w-2.5 md:h-2.5 bg-accent-gold rounded-full animate-pulse animation-delay-600"></div>
            </div>
            <p class="text-text-muted text-sm md:text-base">{{ __('ui.lobby.waiting_narrator') }}</p>
        </div>
    </div>
</div>
