<div class="glass-panel border border-border-default p-5 w-full animate-fadeInUp"
     style="touch-action: manipulation;">

    {{-- MASKED STATE: Identical for every player, before and after action --}}
    @unless($panelOpen)
        <div class="text-center py-4 space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-accent-blue/10 border-2 border-accent-blue/30 flex items-center justify-center animate-floatSlow">
                <span class="text-2xl">🌙</span>
            </div>
            <p class="text-text-primary font-medium">{{ __('ui.role.night_phase') }}</p>
            <p class="text-text-muted text-sm">{{ __('ui.role.night_wait') }}</p>
            <button wire:click="openPanel"
                    class="mt-2 px-6 py-3 bg-accent-blue/20 border border-accent-blue/40 text-text-primary font-medium rounded-lg hover:bg-accent-blue/30 hover:border-accent-blue/60 transition-all duration-200 text-sm active:scale-95">
                {{ __('ui.night.open_private_action') }}
            </button>
        </div>
    @endunless

    {{-- OPEN STATE --}}
    @if($panelOpen)
        <div x-data x-transition:enter="transition-all duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Close button --}}
            <div class="flex justify-end mb-2">
                <button wire:click="closePanel"
                        class="text-text-muted hover:text-text-primary text-lg leading-none px-2 py-1 rounded hover:bg-bg-elevated transition-colors">
                    &times;
                </button>
            </div>

            {{-- WITCH: Player list + Save/Poison buttons --}}
            @if($isWitch)
                <div class="space-y-4">
                    <p class="text-text-primary font-medium text-center">{{ __('ui.action.choose_target') }}</p>
                    <p class="text-text-muted text-xs text-center">{{ __('ui.night.choose_carefully') }}</p>
                    <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                        @foreach($alivePlayers as $p)
                            @php $isSelected = $selectedTargetId === $p['id']; @endphp
                            <button wire:click="selectTarget('{{ $p['id'] }}')"
                                    class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-start flex items-center gap-3 group
                                           {{ $isSelected ? 'border-accent-gold/60 bg-accent-gold/10' : '' }}">
                                <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-gold transition-colors">
                                    {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                                </div>
                                <span class="font-medium">{{ $p['nickname'] }}</span>
                                @if($isSelected)
                                    <span class="ml-auto text-accent-gold text-sm">✓</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Save and Poison buttons --}}
                    @if($selectedTargetId)
                        <div class="flex gap-3 justify-center pt-2">
                            @if(!$witchSaveUsed)
                                <button wire:click="witchUseSave"
                                        @if($actionCompleted) disabled @endif
                                        class="flex-1 px-5 py-2.5 bg-bg-surface border border-border-default text-text-primary font-medium rounded-lg hover:bg-bg-elevated hover:border-border-default transition-all duration-200 text-sm active:scale-95 {{ $actionCompleted ? 'opacity-40 cursor-not-allowed' : '' }}">
                                    {{ __('ui.action.save') }}
                                </button>
                            @endif
                            @if(!$witchPoisonUsed)
                                <button wire:click="witchUsePoison"
                                        @if($actionCompleted) disabled @endif
                                        class="flex-1 px-5 py-2.5 bg-bg-surface border border-border-default text-text-primary font-medium rounded-lg hover:bg-bg-elevated hover:border-border-default transition-all duration-200 text-sm active:scale-95 {{ $actionCompleted ? 'opacity-40 cursor-not-allowed' : '' }}">
                                    {{ __('ui.action.poison') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

            {{-- All other roles (including Werewolf): Generic target selection + confirm --}}
            @else
                @if($confirming && $selectedTargetId)
                    <div class="text-center py-4 space-y-5">
                        <p class="text-text-muted text-sm">{{ __('ui.action.confirm_action') }}</p>
                        @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
                        <div class="glass-panel border border-border-default p-4">
                            <p class="text-text-primary text-xl font-semibold">{{ $targetName }}</p>
                        </div>
                        <div class="flex gap-4 justify-center">
                            <button wire:click="cancelSelection"
                                    @if($actionCompleted) disabled @endif
                                    class="px-6 py-2.5 bg-bg-surface border border-border-default text-text-primary font-medium rounded-lg hover:bg-bg-elevated hover:border-border-default transition-all duration-200 text-sm active:scale-95 {{ $actionCompleted ? 'opacity-40 cursor-not-allowed' : '' }}">
                                {{ __('ui.button.cancel') }}
                            </button>
                            @if($isWerewolfFaction)
                                <button wire:click="wolfConfirmKill"
                                        @if($actionCompleted) disabled @endif
                                        class="px-6 py-2.5 bg-bg-surface border border-border-default text-text-primary font-medium rounded-lg hover:bg-bg-elevated hover:border-border-default transition-all duration-200 text-sm active:scale-95 {{ $actionCompleted ? 'opacity-40 cursor-not-allowed' : '' }}">
                                    {{ __('ui.button.confirm') }}
                                </button>
                            @else
                                <button wire:click="confirmAction"
                                        @if($actionCompleted) disabled @endif
                                        class="px-6 py-2.5 bg-bg-surface border border-border-default text-text-primary font-medium rounded-lg hover:bg-bg-elevated hover:border-border-default transition-all duration-200 text-sm active:scale-95 {{ $actionCompleted ? 'opacity-40 cursor-not-allowed' : '' }}">
                                    {{ __('ui.button.confirm') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <p class="text-text-primary font-medium text-center">{{ __('ui.action.choose_target') }}</p>
                        <p class="text-text-muted text-xs text-center">{{ __('ui.night.choose_carefully') }}</p>
                        <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                            @forelse($alivePlayers as $target)
                                @php $isSelected = $selectedTargetId === $target['id']; @endphp
                                <button wire:click="{{ $isWerewolfFaction ? 'wolfSelectTarget' : 'selectTarget' }}('{{ $target['id'] }}')"
                                        @if($actionCompleted) disabled @endif
                                        class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-start flex items-center gap-3 group
                                               {{ $isSelected ? 'border-accent-gold/60 bg-accent-gold/10' : '' }} {{ $actionCompleted ? 'opacity-40 cursor-not-allowed' : '' }}">
                                    <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-gold transition-colors">
                                        {{ strtoupper(substr($target['nickname'], 0, 1)) }}
                                    </div>
                                    <span class="font-medium flex-1 text-text-primary">{{ $target['nickname'] }}</span>
                                    @if($isSelected)
                                        <span class="text-accent-gold text-sm">✓</span>
                                    @endif
                                </button>
                            @empty
                                <p class="text-text-muted text-sm text-center py-4">{{ __('ui.werewolf.no_targets') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
