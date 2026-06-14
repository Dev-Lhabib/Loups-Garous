<div class="glass-panel border border-accent-gold/30 p-5 w-full animate-slideUpReveal">
    @if($banned)
        <div class="text-center py-6">
            <div class="text-3xl mb-2 animate-floatSlow">🚫</div>
            <p class="text-text-muted font-semibold">{{ __('ui.vote.banned') }}</p>
        </div>
    @elseif($submitted)
        <div class="text-center py-6 space-y-4">
            <div class="text-3xl animate-floatSlow">🗳️</div>
            <p class="text-text-muted font-medium">{{ __('ui.vote.submitted') }}</p>
            <div class="flex justify-center gap-2">
                <span class="w-2 h-2 rounded-full bg-accent-gold/50 animate-pulse animation-delay-200"></span>
                <span class="w-2 h-2 rounded-full bg-accent-gold/50 animate-pulse animation-delay-400"></span>
                <span class="w-2 h-2 rounded-full bg-accent-gold/50 animate-pulse animation-delay-600"></span>
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
