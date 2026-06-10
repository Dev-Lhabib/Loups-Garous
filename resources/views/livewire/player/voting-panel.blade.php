<div wire:poll.3s class="glass-panel border border-accent-gold/30 p-5 w-full animate-slideUpReveal"
     x-data="{ revealed: false }">
    @if($banned)
        <div class="text-center py-6">
            <div class="text-3xl mb-2 animate-floatSlow">🚫</div>
            <p class="text-text-muted font-semibold">{{ __('ui.vote.banned') }}</p>
        </div>
    @elseif($submitted)
        <div class="text-center"
             x-on:mousedown="revealed = true"
             x-on:mouseup="revealed = false"
             x-on:mouseleave="revealed = false"
             x-on:touchstart="revealed = true"
             x-on:touchend="revealed = false">
            <div x-show="!revealed" class="py-6 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-accent-gold/10 border-2 border-accent-gold/30 flex items-center justify-center animate-scaleCheck">
                    <span class="text-3xl text-accent-gold">✓</span>
                </div>
                <p class="text-text-muted font-medium">{{ __('ui.vote.submitted') }}</p>
                <div class="flex justify-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-accent-gold/50 animate-pulse animation-delay-200"></span>
                    <span class="w-2 h-2 rounded-full bg-accent-gold/50 animate-pulse animation-delay-400"></span>
                    <span class="w-2 h-2 rounded-full bg-accent-gold/50 animate-pulse animation-delay-600"></span>
                </div>
                <p class="text-text-muted/40 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
            </div>
            <div x-show="revealed" x-cloak
                 x-transition:enter="transition-all duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="py-4 space-y-2">
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __('ui.vote.title') }}</p>
                @php $submittedVote = \App\Models\Vote::where('game_state_id', $room->gameState->id)->where('voter_id', $player->id)->first(); @endphp
                @if($submittedVote && $submittedVote->target)
                    <p class="text-accent-gold text-lg font-semibold">{{ $submittedVote->target->nickname }}</p>
                @endif
            </div>
        </div>
    @elseif($confirming && $selectedTargetId)
        <div class="text-center py-4 space-y-5 animate-fadeInScale">
            <p class="text-text-muted text-sm">{{ __('ui.vote.confirm') }}</p>
            @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
            <div class="glass-panel border border-accent-red/40 p-4">
                <p class="text-text-primary text-xl font-semibold">{{ $targetName }}</p>
            </div>
            <div class="flex gap-4 justify-center">
                <button wire:click="cancelSelection"
                        class="px-6 py-2.5 bg-bg-elevated text-text-secondary rounded-lg hover:bg-border-default transition-all duration-200 text-sm font-medium hover:scale-105 active:scale-95">
                    {{ __('ui.button.cancel') }}
                </button>
                <button wire:click="confirmVote"
                         class="px-6 py-2.5 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm shadow-lg hover:scale-105 active:scale-95">
                    {{ __('ui.button.confirm') }}
                </button>
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex flex-col items-center gap-3 text-center">
                <span class="text-3xl animate-floatSlow">🗳️</span>
                <div>
                    <p class="text-text-primary font-medium">{{ __('ui.vote.title') }}</p>
                    <p class="text-text-muted text-xs mt-1">{{ __('ui.action.choose_carefully') }}</p>
                </div>
            </div>

            {{-- Live tally --}}
            @if(count($liveTally) > 0)
                <div class="space-y-1">
                    @foreach($liveTally as $t)
                        <div class="flex justify-between text-sm px-3 py-2 bg-bg-surface/50 rounded-lg border border-border-default">
                            <span class="text-text-primary">{{ $t['nickname'] }}</span>
                            <span class="text-accent-gold font-mono font-bold">{{ $t['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Target list --}}
            <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                @foreach($alivePlayers as $p)
                    <button wire:click="selectTarget('{{ $p['id'] }}')"
                            class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-start flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-gold transition-colors">
                            {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                        </div>
                        <span class="font-medium group-hover:translate-x-[var(--tx-hover)] transition-transform duration-150">{{ $p['nickname'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
