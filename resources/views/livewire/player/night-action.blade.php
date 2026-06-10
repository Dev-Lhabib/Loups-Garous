<div wire:poll.3s class="glass-panel border border-border-default p-5 w-full animate-fadeInUp"
     x-data="{ revealed: false }">
    @if($submitted && $submittedAction && !$wantsMoreActions)
        {{-- Real action submitted - cinematic success --}}
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
                <p class="text-text-muted font-medium">{{ __('ui.action.submitted') }}</p>
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
                 class="py-4 space-y-3">
                <x-role-icon :roleKey="$role->key" class="text-3xl animate-floatSlow" />
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __("roles.{$role->key}.name") }}</p>
                <p class="text-text-primary text-lg font-semibold">{{ __("ui.action.{$submittedAction->action_type}") }}</p>
                @if($submittedAction->target)
                    <p class="text-accent-gold font-medium animate-fadeInScale">{{ $submittedAction->target->nickname }}</p>
                @endif
            </div>
        </div>
    @elseif($isDecoy)
        {{-- Decoy puzzle --}}
        <div class="text-center py-4 space-y-4"
             x-data="{ puzzleRevealed: false }"
             x-on:mousedown="puzzleRevealed = true"
             x-on:mouseup="puzzleRevealed = false"
             x-on:mouseleave="puzzleRevealed = false"
             x-on:touchstart="puzzleRevealed = true"
             x-on:touchend="puzzleRevealed = false">
            <div x-show="!puzzleRevealed" class="space-y-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-accent-gold/10 border-2 border-accent-gold/30 flex items-center justify-center">
                    <span class="text-2xl">🧩</span>
                </div>
                <p class="text-text-muted text-sm">{{ __('ui.game.decoy_prompt') }}</p>
                <div class="bg-bg-surface/50 rounded-lg p-4 border border-border-default">
                    <p class="text-text-primary text-base font-medium">{{ $decoyPuzzle['content'] ?? '' }}</p>
                </div>
                <button wire:click="refreshDecoy"
                        class="text-xs text-accent-gold hover:text-accent-gold-dark transition-colors bg-accent-gold/10 hover:bg-accent-gold/20 px-3 py-1.5 rounded-lg font-medium">
                    {{ __('ui.action.next_puzzle') }}
                </button>
                <p class="text-text-muted/40 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
            </div>
            <div x-show="puzzleRevealed" x-cloak
                 x-transition:enter="transition-all duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="py-4 space-y-2">
                <x-role-icon :roleKey="$role?->key ?? 'villager'" class="text-3xl animate-floatSlow" />
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __('ui.game.decoy_prompt') }}</p>
                <p class="text-accent-gold font-medium">{{ $player->nickname }}</p>
            </div>
        </div>
    @elseif($confirming && $selectedTargetId)
        {{-- Confirmation --}}
        <div class="text-center py-4 space-y-5 animate-fadeInScale">
            <p class="text-text-muted text-sm">{{ __('ui.action.confirm_action') }}</p>
            @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
            <div class="glass-panel border border-accent-gold/40 p-4 glow-gold">
                <p class="text-text-primary text-xl font-semibold">{{ $targetName }}</p>
            </div>
            <div class="flex gap-4 justify-center">
                <button wire:click="cancelSelection"
                        class="px-6 py-2.5 bg-bg-elevated text-text-secondary rounded-lg hover:bg-border-default transition-all duration-200 text-sm font-medium hover:scale-105 active:scale-95">
                    {{ __('ui.button.cancel') }}
                </button>
                <button wire:click="confirmSubmit"
                        class="px-6 py-2.5 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm font-medium shadow-lg hover:scale-105 active:scale-95">
                    {{ __('ui.button.confirm') }}
                </button>
            </div>
        </div>
    @elseif(!$actionSelected && $isMultiAction)
        {{-- Multi-action role: choose action type first --}}
        <div class="space-y-4 text-center py-4">
            <div class="flex flex-col items-center gap-3">
                <x-role-icon :roleKey="$role->key" class="text-3xl animate-floatSlow" />
                <p class="text-text-primary font-medium">{{ __("roles.{$role->key}.action_prompt") }}</p>
            </div>
            <div class="space-y-2">
                @foreach($actionTypes as $at)
                    <button wire:click="selectActionType('{{ $at }}')"
                            class="w-full px-4 py-4 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-left flex items-center gap-3 group">
                        <span class="w-10 h-10 rounded-full bg-bg-elevated flex items-center justify-center text-lg group-hover:text-accent-gold transition-colors">
                            @switch($at)
                                @case('save') 🧪 @break
                                @case('poison') ☠️ @break
                                @default 🔮
                            @endswitch
                        </span>
                        <span class="font-medium">{{ __("ui.action.{$at}") }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @elseif($submitted && $wantsMoreActions)
        {{-- Multi-action: first action done, offer second --}}
        <div class="text-center py-6 space-y-4 animate-fadeInScale">
            <div class="w-16 h-16 mx-auto rounded-full bg-accent-gold/10 border-2 border-accent-gold/30 flex items-center justify-center animate-scaleCheck">
                <span class="text-3xl text-accent-gold">✓</span>
            </div>
            <p class="text-text-muted font-medium">{{ __('ui.action.submitted') }}</p>
            <button wire:click="submitAnother"
                    class="px-6 py-2.5 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm hover:scale-105 active:scale-95">
                {{ __('ui.action.submit_another') }}
            </button>
        </div>
    @else
        {{-- Target selection --}}
        <div class="space-y-4">
            <div class="flex flex-col items-center gap-3 text-center">
                <x-role-icon :roleKey="$role->key" class="text-3xl animate-floatSlow" />
                <div>
                    <p class="text-text-primary font-medium">{{ $isDecoy ? __('ui.action.decoy_select') : __("ui.roles.{$role->key}.action_prompt") }}</p>
                    <p class="text-text-muted text-xs mt-1">{{ __('ui.night.choose_carefully') }}</p>
                </div>
            </div>
            <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                @foreach($alivePlayers as $p)
                    <button wire:click="selectTarget('{{ $p['id'] }}')"
                            class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-left flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-gold transition-colors">
                            {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                        </div>
                        <span class="font-medium group-hover:translate-x-0.5 transition-transform">{{ $p['nickname'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
                <p class="text-text-muted font-medium">{{ __('ui.action.submitted') }}</p>
                <div class="flex justify-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-accent-green/50 animate-pulse animation-delay-200"></span>
                    <span class="w-2 h-2 rounded-full bg-accent-green/50 animate-pulse animation-delay-400"></span>
                    <span class="w-2 h-2 rounded-full bg-accent-green/50 animate-pulse animation-delay-600"></span>
                </div>
                <p class="text-text-muted/40 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
            </div>
            <div x-show="revealed" x-cloak
                 x-transition:enter="transition-all duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="py-4 space-y-3">
                <x-role-icon :roleKey="$role->key" class="text-3xl animate-floatSlow" />
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __("roles.{$role->key}.name") }}</p>
                <p class="text-text-primary text-lg font-semibold">{{ __("ui.action.{$submittedAction->action_type}") }}</p>
                @if($submittedAction->target)
                    <p class="text-accent-gold font-medium animate-fadeInScale">{{ $submittedAction->target->nickname }}</p>
                @endif
            </div>
        </div>
    @elseif($submitted && $isDecoy)
        {{-- Decoy submitted --}}
        <div class="text-center"
             x-on:mousedown="revealed = true"
             x-on:mouseup="revealed = false"
             x-on:mouseleave="revealed = false"
             x-on:touchstart="revealed = true"
             x-on:touchend="revealed = false">
            <div x-show="!revealed" class="py-6 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-accent-blue/10 border-2 border-accent-blue/30 flex items-center justify-center animate-scaleCheck">
                    <span class="text-3xl text-accent-blue">✓</span>
                </div>
                <p class="text-text-muted font-medium">{{ __('ui.action.submitted') }}</p>
                <div class="flex justify-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-accent-blue/50 animate-pulse animation-delay-200"></span>
                    <span class="w-2 h-2 rounded-full bg-accent-blue/50 animate-pulse animation-delay-400"></span>
                    <span class="w-2 h-2 rounded-full bg-accent-blue/50 animate-pulse animation-delay-600"></span>
                </div>
                <p class="text-text-muted/40 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
            </div>
            <div x-show="revealed" x-cloak
                 x-transition:enter="transition-all duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="py-4 space-y-2">
                @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __('ui.action.decoy_submitted') }}</p>
                <p class="text-accent-gold font-medium">{{ $targetName }}</p>
            </div>
        </div>
    @elseif($confirming && $selectedTargetId)
        {{-- Confirmation --}}
        <div class="text-center py-4 space-y-5 animate-fadeInScale">
            <p class="text-text-muted text-sm">{{ __('ui.action.confirm_action') }}</p>
            @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
            <div class="glass-panel border border-accent-gold/40 p-4 glow-gold">
                <p class="text-text-primary text-xl font-semibold">{{ $targetName }}</p>
            </div>
            <div class="flex gap-4 justify-center">
                <button wire:click="cancelSelection"
                        class="px-6 py-2.5 bg-bg-elevated text-text-secondary rounded-lg hover:bg-border-default transition-all duration-200 text-sm font-medium hover:scale-105 active:scale-95">
                    {{ __('ui.button.cancel') }}
                </button>
                <button wire:click="confirmSubmit"
                        class="px-6 py-2.5 bg-accent-green text-white rounded-lg hover:bg-accent-green/90 transition-all duration-200 text-sm font-medium shadow-lg hover:scale-105 active:scale-95">
                    {{ __('ui.button.confirm') }}
                </button>
            </div>
        </div>
    @else
        {{-- Target selection --}}
        <div class="space-y-4">
            <div class="flex flex-col items-center gap-3 text-center">
                <x-role-icon :roleKey="$role->key" class="text-3xl animate-floatSlow" />
                <div>
                    <p class="text-text-primary font-medium">{{ $isDecoy ? __('ui.action.decoy_select') : __("ui.roles.{$role->key}.action_prompt") }}</p>
                    <p class="text-text-muted text-xs mt-1">{{ __('ui.action.choose_carefully') }}</p>
                </div>
            </div>
            <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                @foreach($alivePlayers as $p)
                    <button wire:click="selectTarget('{{ $p['id'] }}')"
                            class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-left flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-gold transition-colors">
                            {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                        </div>
                        <span class="font-medium group-hover:translate-x-0.5 transition-transform">{{ $p['nickname'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
