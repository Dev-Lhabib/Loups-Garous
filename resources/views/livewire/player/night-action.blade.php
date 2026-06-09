<div wire:poll.3s class="glass-panel border border-border-default p-5 w-full animate-fadeInUp"
     x-data="{ revealed: false }">
    @if($submitted && $submittedAction)
        {{-- Real action submitted --}}
        <div class="text-center"
             x-on:mousedown="revealed = true"
             x-on:mouseup="revealed = false"
             x-on:mouseleave="revealed = false"
             x-on:touchstart="revealed = true"
             x-on:touchend="revealed = false">
            <div x-show="!revealed" class="py-6 space-y-3">
                <div class="w-16 h-16 mx-auto rounded-full bg-accent-green/10 border-2 border-accent-green/30 flex items-center justify-center">
                    <span class="text-3xl text-accent-green">✓</span>
                </div>
                <p class="text-text-muted">{{ __('ui.action.submitted') }}</p>
                <p class="text-text-muted/50 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
            </div>
            <div x-show="revealed" x-cloak class="py-4 space-y-2">
                <x-role-icon :roleKey="$role->key" class="text-2xl" />
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __("roles.{$role->key}.name") }}</p>
                <p class="text-text-primary text-lg font-semibold">{{ __("ui.action.{$submittedAction->action_type}") }}</p>
                @if($submittedAction->target)
                    <p class="text-accent-gold font-medium">{{ $submittedAction->target->nickname }}</p>
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
            <div x-show="!revealed" class="py-6 space-y-3">
                <div class="w-16 h-16 mx-auto rounded-full bg-accent-blue/10 border-2 border-accent-blue/30 flex items-center justify-center">
                    <span class="text-3xl text-accent-blue">✓</span>
                </div>
                <p class="text-text-muted">{{ __('ui.action.submitted') }}</p>
                <p class="text-text-muted/50 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
            </div>
            <div x-show="revealed" x-cloak class="py-4 space-y-2">
                @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
                <p class="text-text-muted text-xs uppercase tracking-widest">{{ __('ui.action.decoy_submitted') }}</p>
                <p class="text-accent-gold font-medium">{{ $targetName }}</p>
            </div>
        </div>
    @elseif($confirming && $selectedTargetId)
        <div class="text-center py-4 space-y-4">
            <p class="text-text-muted">{{ __('ui.action.confirm_action') }}</p>
            @php $targetName = collect($alivePlayers)->firstWhere('id', $selectedTargetId)['nickname'] ?? ''; @endphp
            <div class="glass-panel border border-accent-gold/30 p-4">
                <p class="text-text-primary text-xl font-semibold">{{ $targetName }}</p>
            </div>
            <div class="flex gap-4 justify-center">
                <button wire:click="cancelSelection" class="px-6 py-2.5 bg-bg-elevated text-text-secondary rounded-lg hover:bg-border-default transition-colors text-sm font-medium">{{ __('ui.button.cancel') }}</button>
                <button wire:click="confirmSubmit" class="px-6 py-2.5 bg-accent-green text-white rounded-lg hover:bg-accent-green/90 transition-colors text-sm font-medium shadow-lg">{{ __('ui.button.confirm') }}</button>
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center gap-2 text-center justify-center">
                <x-role-icon :roleKey="$role->key" class="text-lg" />
                <p class="text-text-muted text-sm">{{ $isDecoy ? __('ui.action.decoy_select') : __("ui.roles.{$role->key}.action_prompt") }}</p>
            </div>
            <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                @foreach($alivePlayers as $p)
                    <button wire:click="selectTarget('{{ $p['id'] }}')"
                            class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-left flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-gold transition-colors">
                            {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                        </div>
                        <span class="font-medium">{{ $p['nickname'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
