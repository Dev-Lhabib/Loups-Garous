<div wire:poll.3s class="glass-panel border border-accent-red/30 p-5 w-full animate-fadeInUp"
     x-data="{ revealed: false }">
    <div class="space-y-4">
        {{-- Header --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-accent-red/10 border-2 border-accent-red/30 flex items-center justify-center">
                <span class="text-xl">🐺</span>
            </div>
            <div>
                <p class="text-text-primary font-semibold text-sm">{{ __('ui.action.kill') }}</p>
                <p class="text-text-muted text-xs">{{ __('ui.night.choose_carefully') }}</p>
            </div>
        </div>

        {{-- Werewolf selections summary --}}
        @if(count($wolfSelections) > 0)
            <div class="bg-bg-surface/50 rounded-lg p-3 border border-border-default">
                <p class="text-text-muted text-[10px] uppercase tracking-widest mb-2">{{ __('ui.werewolf.selections') }}</p>
                <div class="space-y-1">
                    @foreach($wolfSelections as $wolfId => $targetId)
                        @php
                            $wolfName = $this->getWolfName($wolfId);
                            $target = collect($aliveTargets)->firstWhere('id', $targetId);
                            $targetName = $target['nickname'] ?? '—';
                        @endphp
                        <div class="flex justify-between text-xs">
                            <span class="text-text-secondary">{{ $wolfName }}</span>
                            <span class="{{ $targetId ? 'text-accent-red font-medium' : 'text-text-muted' }}">
                                {{ $target ? $targetName : __('ui.game.waiting') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Agreement indicator --}}
        @if($allAgree && $agreedTargetId)
            <div class="bg-accent-green/10 border border-accent-green/30 rounded-lg p-3 text-center">
                @php $agreeTarget = collect($aliveTargets)->firstWhere('id', $agreedTargetId); @endphp
                <p class="text-accent-green text-xs font-semibold">{{ __('ui.werewolf.all_agree') }}</p>
                <p class="text-text-primary text-lg font-bold mt-1">{{ $agreeTarget['nickname'] ?? '' }}</p>
            </div>
        @endif

        {{-- Target list --}}
        <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
            @forelse($aliveTargets as $target)
                @php
                    $isSelected = $selectedTargetId === $target['id'];
                    $selectionCount = collect($wolfSelections)->filter(fn($t) => $t === $target['id'])->count();
                @endphp
                <button wire:click="selectTarget('{{ $target['id'] }}')"
                        class="w-full px-4 py-3 rounded-lg text-start flex items-center gap-3 group transition-all duration-200
                               {{ $isSelected
                                   ? 'bg-accent-red/20 border-2 border-accent-red/60 glow-red'
                                   : 'bg-bg-surface/50 border border-border-default hover:bg-bg-elevated hover:border-accent-red/30' }}">
                    <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-red transition-colors">
                        {{ strtoupper(substr($target['nickname'], 0, 1)) }}
                    </div>
                    <span class="font-medium flex-1 text-text-primary group-hover:translate-x-[var(--tx-hover)] transition-transform duration-150">
                        {{ $target['nickname'] }}
                    </span>
                    @if($selectionCount > 0)
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-accent-red/20 text-accent-red font-semibold">
                            {{ $selectionCount }}
                        </span>
                    @endif
                </button>
            @empty
                <p class="text-text-muted text-sm text-center py-4">{{ __('ui.werewolf.no_targets') }}</p>
            @endforelse
        </div>

        {{-- Confirm button --}}
        @if($allAgree && $agreedTargetId && !$submitted)
            <button wire:click="confirmKill"
                    class="w-full py-3 bg-accent-red text-white font-bold rounded-lg hover:bg-accent-red-dark transition-all duration-200 text-sm shadow-lg hover:scale-[1.02] active:scale-95">
                {{ __('ui.werewolf.confirm_kill') }}
            </button>
        @endif

        {{-- Submitted state --}}
        @if($submitted)
            <div class="text-center py-4 space-y-2"
                 x-on:mousedown="revealed = true"
                 x-on:mouseup="revealed = false"
                 x-on:mouseleave="revealed = false"
                 x-on:touchstart="revealed = true"
                 x-on:touchend="revealed = false">
                <div x-show="!revealed" class="space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-full bg-accent-red/10 border-2 border-accent-red/30 flex items-center justify-center animate-scaleCheck">
                        <span class="text-2xl">🐺</span>
                    </div>
                    <p class="text-text-muted text-sm">{{ __('ui.action.submitted') }}</p>
                    <p class="text-text-muted/40 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
                </div>
                <div x-show="revealed" x-cloak class="space-y-1">
                    <p class="text-text-primary font-semibold">{{ __('ui.action.kill') }}</p>
                    @php $target = collect($aliveTargets)->firstWhere('id', $agreedTargetId); @endphp
                    @if($target)
                        <p class="text-accent-gold">{{ $target['nickname'] }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
