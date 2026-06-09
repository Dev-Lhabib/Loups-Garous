<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm mx-auto text-center space-y-8 animate-fadeInUp">

        {{-- Room Code --}}
        <div class="glass-panel border border-accent-gold/30 inline-block px-6 py-3">
            <p class="text-text-muted text-xs uppercase tracking-widest mb-1">{{ __('ui.lobby.room_code') }}</p>
            <p class="font-mono text-2xl tracking-[0.3em] text-accent-gold font-bold">{{ $room->code }}</p>
        </div>

        {{-- Player Identity --}}
        <div class="space-y-3">
            <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-accent-gold/30 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center">
                <span class="text-2xl font-bold text-accent-gold">{{ strtoupper(substr($player->nickname, 0, 1)) }}</span>
            </div>
            <p class="text-text-muted text-sm">{{ __('ui.lobby.you_joined_as') }}</p>
            <p class="font-serif text-2xl text-accent-gold font-bold">{{ $player->nickname }}</p>
        </div>

        {{-- Players --}}
        <div>
            <h3 class="text-text-muted text-sm uppercase tracking-wider mb-4 flex items-center justify-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-accent-gold"></span>
                <span>{{ __('ui.lobby.connected_players') }}</span>
            </h3>
            <livewire:shared.player-list :room="$room" :wire:key="'player-list-'.$room->id" />
        </div>

        {{-- Waiting State --}}
        <div class="space-y-4 animate-fadeInUp">
            <div class="flex items-center justify-center gap-2">
                <div class="w-2.5 h-2.5 bg-accent-gold rounded-full animate-pulse animation-delay-200"></div>
                <div class="w-2.5 h-2.5 bg-accent-gold rounded-full animate-pulse animation-delay-400"></div>
                <div class="w-2.5 h-2.5 bg-accent-gold rounded-full animate-pulse animation-delay-600"></div>
            </div>
            <p class="text-text-muted">{{ __('ui.lobby.waiting_narrator') }}</p>
        </div>
    </div>
</div>
