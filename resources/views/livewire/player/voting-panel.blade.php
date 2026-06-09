<div wire:poll.3s class="glass-panel border border-accent-red/30 p-5 w-full animate-fadeInUp"
     x-data="{ revealed: false }">
    @if($banned)
        <div class="text-center py-6">
            <div class="text-3xl mb-2">🚫</div>
            <p class="text-accent-red font-semibold">{{ __('ui.vote.banned') }}</p>
        </div>
    @elseif($submitted)
        <div class="text-center"
             x-on:mousedown="revealed = true"
             x-on:mouseup="revealed = false"
             x-on:mouseleave="revealed = false"
             x-on:touchstart="revealed = true"
             x-on:touchend="revealed = false">
            <div x-show="!revealed" class="py-6 space-y-3">
                <div class="w-16 h-16 mx-auto rounded-full bg-accent-red/10 border-2 border-accent-red/30 flex items-center justify-center">
                    <span class="text-3xl text-accent-red">✓</span>
                </div>
                <p class="text-text-muted">{{ __('ui.vote.submitted') }}</p>
                <p class="text-text-muted/50 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
            </div>
            <div x-show="revealed" x-cloak class="py-4 space-y-2">
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __('ui.vote.title') }}</p>
                @php $submittedVote = \App\Models\Vote::where('game_state_id', $room->gameState->id)->where('voter_id', $player->id)->first(); @endphp
                @if($submittedVote && $submittedVote->target)
                    <p class="text-accent-gold text-lg font-semibold">{{ $submittedVote->target->nickname }}</p>
                @endif
            </div>
        </div>
    @elseif($confirming && $selectedTargetId)
        <div class="text-center py-4 space-y-4">
            <p class="text-text-muted">{{ __('ui.vote.confirm') }}</p>
            @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
            <div class="glass-panel border border-accent-red/40 p-4">
                <p class="text-text-primary text-xl font-semibold">{{ $targetName }}</p>
            </div>
            <div class="flex gap-4 justify-center">
                <button wire:click="cancelSelection" class="px-6 py-2.5 bg-bg-elevated text-text-secondary rounded-lg hover:bg-border-default transition-colors text-sm font-medium">{{ __('ui.button.cancel') }}</button>
                <button wire:click="confirmVote" class="px-6 py-2.5 bg-accent-red text-white rounded-lg hover:bg-accent-red-dark transition-colors text-sm font-medium shadow-lg">{{ __('ui.button.confirm') }}</button>
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center gap-2 justify-center">
                <span class="text-lg">🗳️</span>
                <p class="text-text-primary font-medium">{{ __('ui.vote.title') }}</p>
            </div>

            {{-- Live tally --}}
            @if(count($liveTally) > 0)
                <div class="space-y-1">
                    @foreach($liveTally as $t)
                        <div class="flex justify-between text-sm px-3 py-2 bg-bg-surface/50 rounded-lg border border-border-default">
                            <span class="text-text-primary">{{ $t['nickname'] }}</span>
                            <span class="text-accent-red font-mono font-bold">{{ $t['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Target list --}}
            <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                @foreach($alivePlayers as $p)
                    <button wire:click="selectTarget('{{ $p['id'] }}')"
                            class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-red/30 border border-border-default transition-all duration-200 text-left flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-red transition-colors">
                            {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                        </div>
                        <span class="font-medium">{{ $p['nickname'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
