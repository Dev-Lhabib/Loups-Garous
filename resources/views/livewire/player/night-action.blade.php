<div wire:poll.3s class="glass-panel border border-border-default p-5 w-full animate-fadeInUp">

    {{-- DEFAULT STATE: Generic night phase (identical for all players) --}}
    <div class="text-center py-6 space-y-4"
         x-data="{
             holding: false,
             holdTimer: null,
             startHold() {
                 this.holding = true;
                 this.holdTimer = setTimeout(() => {
                     $wire.revealPanel();
                     if (navigator.vibrate) navigator.vibrate(30);
                 }, 800);
             },
             endHold() {
                 this.holding = false;
                 if (this.holdTimer) {
                     clearTimeout(this.holdTimer);
                     this.holdTimer = null;
                 }
                 $wire.hidePanel();
             }
         }"
         x-on:mousedown.prevent="startHold()"
         x-on:mouseup="endHold()"
         x-on:mouseleave="endHold()"
         x-on:touchstart.prevent="startHold()"
         x-on:touchend="endHold()"
         x-on:touchcancel="endHold()">

        {{-- Masked face: Generic night phase message --}}
        <div x-show="!$wire.panelRevealed">
            <div class="w-14 h-14 mx-auto rounded-full bg-accent-blue/10 border-2 border-accent-blue/30 flex items-center justify-center animate-floatSlow"
                 :class="holding ? 'scale-110 border-accent-blue/60' : ''"
                 style="transition: all 0.3s ease;">
                <span class="text-2xl">🌙</span>
            </div>
            <p class="text-text-primary font-medium mt-3">{{ __('ui.role.night_phase') }}</p>
            <p class="text-text-muted text-sm mt-1">{{ __('ui.role.night_wait') }}</p>
            <p class="text-text-muted/40 text-xs mt-3">{{ __('ui.role.hold_to_reveal') }}</p>

            {{-- Hold progress indicator --}}
            <div x-show="holding" x-cloak class="mt-3 flex justify-center">
                <div class="w-16 h-1 bg-bg-elevated rounded-full overflow-hidden">
                    <div class="h-full bg-accent-blue rounded-full"
                         style="transition: width 0.8s linear;"
                         :style="holding ? 'width: 100%' : 'width: 0%'"></div>
                </div>
            </div>
        </div>

        {{-- Revealed face: Role-specific action panel --}}
        <div x-show="$wire.panelRevealed" x-cloak
             x-transition:enter="transition-all duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            @if($submitted && $submittedAction && !$wantsMoreActions)
                @if(!$hasNightAction)
                    {{-- Passive role completed state --}}
                    <div class="py-6 space-y-3">
                        <div class="w-16 h-16 mx-auto rounded-full bg-accent-green/10 border-2 border-accent-green/30 flex items-center justify-center animate-scaleCheck">
                            <span class="text-3xl text-accent-green">✓</span>
                        </div>
                        <p class="text-text-primary font-semibold">{{ __('ui.action.completed') }}</p>
                        <p class="text-text-muted text-xs">{{ __('ui.action.passive_completed_hint') }}</p>
                    </div>
                @else
                    {{-- Action submitted - hold to see details --}}
                    <div x-data="{ showDetails: false }"
                         x-on:mousedown.prevent="showDetails = true"
                         x-on:mouseup="showDetails = false"
                         x-on:mouseleave="showDetails = false"
                         x-on:touchstart.prevent="showDetails = true"
                         x-on:touchend="showDetails = false">
                        <div x-show="!showDetails" class="py-4 space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-accent-gold/10 border-2 border-accent-gold/30 flex items-center justify-center animate-scaleCheck">
                                <span class="text-2xl text-accent-gold">✓</span>
                            </div>
                            <p class="text-text-muted font-medium">{{ __('ui.action.submitted') }}</p>
                            <p class="text-text-muted/40 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
                        </div>
                        <div x-show="showDetails" x-cloak class="py-4 space-y-3">
                            @if($roleData)
                                <div class="text-3xl">{{ $roleData['key'] === 'werewolf' ? '🐺' : ($roleData['key'] === 'seer' ? '👁️' : '🔮') }}</div>
                                <p class="text-text-primary text-lg font-semibold">{{ $roleData['name'] }}</p>
                                <p class="text-accent-gold font-medium">{{ __('ui.action.' . $submittedAction->action_type) }}</p>
                                @if($submittedAction->target)
                                    <p class="text-accent-gold animate-fadeInScale">{{ $submittedAction->target->nickname }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

            @elseif(!$hasNightAction)
                {{-- No night action: Confirm button first, then decoy --}}
                @if($submitted && $submittedAction)
                    {{-- Completed state --}}
                    <div class="py-6 space-y-3">
                        <div class="w-16 h-16 mx-auto rounded-full bg-accent-green/10 border-2 border-accent-green/30 flex items-center justify-center animate-scaleCheck">
                            <span class="text-3xl text-accent-green">✓</span>
                        </div>
                        <p class="text-text-primary font-semibold">{{ __('ui.action.completed') }}</p>
                        <p class="text-text-muted text-xs">{{ __('ui.action.passive_completed_hint') }}</p>
                    </div>
                @elseif($passiveConfirmed)
                    <div class="py-6 space-y-3">
                        <div class="w-16 h-16 mx-auto rounded-full bg-accent-green/10 border-2 border-accent-green/30 flex items-center justify-center animate-scaleCheck">
                            <span class="text-3xl text-accent-green">✓</span>
                        </div>
                        <p class="text-text-primary font-semibold">{{ __('ui.action.completed') }}</p>
                        <p class="text-text-muted text-xs">{{ __('ui.action.passive_completed_hint') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-accent-purple/10 border-2 border-accent-purple/30 flex items-center justify-center animate-floatSlow">
                                <span class="text-2xl">🧩</span>
                            </div>
                            <p class="text-text-primary font-medium">{{ __('ui.action.decoy_title') }}</p>
                            <p class="text-text-muted text-xs">{{ __('ui.action.decoy_subtitle') }}</p>
                        </div>
                        <button wire:click="confirmPassive"
                                class="w-full py-3 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm shadow-lg hover:scale-[1.02] active:scale-95">
                            ✓ {{ __('ui.action.complete_action') }}
                        </button>
                        <div x-data="{ showPuzzle: false }">
                            <button @click="showPuzzle = !showPuzzle"
                                    class="w-full text-center text-xs text-text-muted hover:text-accent-gold transition-colors">
                                🧩 {{ __('ui.action.decoy_puzzle') }}
                            </button>
                            <div x-show="showPuzzle" x-cloak x-transition:enter="transition-all duration-200" class="mt-3 space-y-3">
                                <div class="glass-panel border border-accent-purple/30 p-4">
                                    <p class="text-text-muted text-xs uppercase tracking-widest mb-2">{{ $decoy['type'] ?? 'puzzle' }}</p>
                                    <p class="text-text-primary text-base">{{ $decoy['content'] ?? '' }}</p>
                                </div>
                                <button wire:click="refreshDecoy"
                                        class="px-4 py-2 bg-bg-surface border border-border-default text-text-muted text-sm rounded-lg hover:bg-bg-elevated hover:text-text-primary transition-all duration-200">
                                    {{ __('ui.action.next_puzzle') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

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
                {{-- Multi-action role: choose action type --}}
                <div class="space-y-4 text-center py-4">
                    @if($roleData)
                        <div class="flex flex-col items-center gap-3">
                            <div class="text-3xl animate-floatSlow">🔮</div>
                            <p class="text-text-primary font-medium">{{ $roleData['action_prompt'] }}</p>
                        </div>
                    @endif
                    <div class="space-y-2">
                        @foreach($actionTypes as $at)
                            <button wire:click="selectActionType('{{ $at }}')"
                                    class="w-full px-4 py-4 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-start flex items-center gap-3 group">
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

            @elseif($role && $role->key === 'wolf_hound' && !$submitted && $hasNightAction)
                {{-- Wolf Hound: choose side --}}
                @if($confirming && $wolfHoundSide)
                    <div class="text-center py-4 space-y-5 animate-fadeInScale">
                        <p class="text-text-muted text-sm">{{ __('ui.action.confirm_action') }}</p>
                        <div class="glass-panel border border-accent-gold/40 p-4 glow-gold">
                            <p class="text-text-primary text-xl font-semibold">
                                {{ $wolfHoundSide === 'werewolves' ? __('ui.factions.werewolves') : __('ui.factions.village') }}
                            </p>
                        </div>
                        <div class="flex gap-4 justify-center">
                            <button wire:click="cancelSelection"
                                    class="px-6 py-2.5 bg-bg-elevated text-text-secondary rounded-lg hover:bg-border-default transition-all duration-200 text-sm font-medium hover:scale-105 active:scale-95">
                                {{ __('ui.button.cancel') }}
                            </button>
                            <button wire:click="confirmWolfHoundSide"
                                    class="px-6 py-2.5 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm font-medium shadow-lg hover:scale-105 active:scale-95">
                                {{ __('ui.button.confirm') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="flex flex-col items-center gap-3 text-center">
                            <div class="text-3xl animate-floatSlow">🐕</div>
                            <div>
                                @if($roleData)
                                    <p class="text-text-primary font-medium">{{ $roleData['action_prompt'] }}</p>
                                @endif
                                <p class="text-text-muted text-xs mt-1">{{ __('ui.night.choose_carefully') }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <button wire:click="selectWolfHoundSide('werewolves')"
                                    class="w-full px-4 py-4 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-accent-red/10 hover:border-accent-red/30 border border-border-default transition-all duration-200 text-start flex items-center gap-3 group">
                                <span class="w-10 h-10 rounded-full bg-bg-elevated flex items-center justify-center text-lg group-hover:text-accent-red transition-colors">🐺</span>
                                <span class="font-medium">{{ __('ui.factions.werewolves') }}</span>
                            </button>
                            <button wire:click="selectWolfHoundSide('villagers')"
                                    class="w-full px-4 py-4 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-accent-blue/10 hover:border-accent-blue/30 border border-border-default transition-all duration-200 text-start flex items-center gap-3 group">
                                <span class="w-10 h-10 rounded-full bg-bg-elevated flex items-center justify-center text-lg group-hover:text-accent-blue transition-colors">🏘️</span>
                                <span class="font-medium">{{ __('ui.factions.village') }}</span>
                            </button>
                        </div>
                    </div>
                @endif

            @else
                {{-- Target selection with visual feedback --}}
                <div class="space-y-4">
                    <div class="flex flex-col items-center gap-3 text-center">
                        <div class="text-3xl animate-floatSlow">🎯</div>
                        <div>
                            @if($roleData)
                                <p class="text-text-primary font-medium">{{ $roleData['action_prompt'] }}</p>
                            @endif
                            <p class="text-text-muted text-xs mt-1">{{ __('ui.night.choose_carefully') }}</p>
                        </div>
                    </div>
                    @if($selectedTargetId)
                        @php $previewTarget = collect($alivePlayers)->firstWhere('id', $selectedTargetId); @endphp
                        <div class="bg-accent-gold/10 border-2 border-accent-gold/50 rounded-xl p-3 text-center animate-fadeInScale">
                            <p class="text-text-muted text-[10px] uppercase tracking-widest">{{ __('ui.action.selected') }}</p>
                            <p class="text-text-primary text-lg font-bold mt-1">{{ $previewTarget['nickname'] ?? '' }}</p>
                        </div>
                    @endif
                    <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                        @foreach($alivePlayers as $p)
                            @php $isSel = $selectedTargetId == $p['id']; @endphp
                            <button wire:click="selectTarget('{{ $p['id'] }}')"
                                    class="w-full px-4 py-3 rounded-lg transition-all duration-200 text-start flex items-center gap-3 group
                                           {{ $isSel
                                               ? 'bg-accent-gold/15 border-2 border-accent-gold/50 text-accent-gold font-semibold shadow-lg scale-[1.02]'
                                               : 'bg-bg-surface/50 text-text-primary hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default' }}">
                                <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold
                                            {{ $isSel ? 'text-accent-gold bg-accent-gold/20' : 'text-text-secondary group-hover:text-accent-gold' }}
                                            transition-colors">
                                    {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                                </div>
                                <span class="font-medium {{ $isSel ? 'text-accent-gold' : 'group-hover:translate-x-0.5' }} transition-transform duration-150">{{ $p['nickname'] }}</span>
                                @if($isSel)
                                    <span class="ms-auto text-accent-gold text-lg">✓</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
