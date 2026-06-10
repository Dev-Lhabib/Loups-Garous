@props([
    'actions' => [],
])

@php
$actionIcons = [
    'kill' => '🐺',
    'extra_kill' => '🐾',
    'convert' => '🦇',
    'solo_kill' => '🌕',
    'protect' => '🛡️',
    'inspect' => '👁️',
    'save' => '🧪',
    'poison' => '☠️',
    'enchant' => '🎵',
    'sniff' => '🦊',
    'link_lovers' => '💘',
    'choose_side' => '🐕',
];
@endphp

<div class="glass-panel border border-accent-blue/30 p-4">
    <h3 class="text-accent-blue text-sm font-semibold mb-3 flex items-center gap-2">
        <span>🌙</span>
        <span>{{ __('ui.narrator.action_feed') }}</span>
        <span class="text-xs text-text-muted bg-accent-blue/10 px-1.5 py-0.5 rounded ms-auto font-mono">{{ count($actions) }}</span>
    </h3>
    <div class="space-y-0 max-h-80 overflow-y-auto scrollbar-thin">
        @forelse($actions as $action)
            @php
                $icon = $actionIcons[$action['action_type']] ?? '❓';
                $ts = isset($action['timestamp']) ? \Carbon\Carbon::parse($action['timestamp'])->isoFormat('HH:mm') : '';
                $roleName = isset($action['role_key']) ? __("roles.{$action['role_key']}.name") : '';
            @endphp
            <div class="relative ps-7 pb-3 group hover:bg-bg-elevated/30 rounded-sm transition-colors">
                <div class="absolute start-2.5 top-1 w-1.5 h-1.5 rounded-full bg-accent-blue/50 ring-2 ring-bg-card"></div>
                @if(!$loop->last)
                    <div class="absolute start-3 top-3 bottom-0 w-px bg-border-default"></div>
                @endif
                <div class="flex items-start gap-2">
                    <span class="text-xs flex-shrink-0 mt-0.5">{{ $icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-text-secondary font-medium truncate">{{ $roleName }}</span>
                            <span class="text-[10px] text-text-muted flex-shrink-0">{{ $ts }}</span>
                        </div>
                        <p class="text-xs text-text-primary mt-0.5">
                            @if($action['action_type'] === 'inspect')
                                {{ __('ui.narrator.action_inspect', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'kill')
                                {{ __('ui.narrator.action_kill', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'protect')
                                {{ __('ui.narrator.action_protect', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'save')
                                {{ __('ui.narrator.action_save', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'poison')
                                {{ __('ui.narrator.action_poison', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'convert')
                                {{ __('ui.narrator.action_convert', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'enchant')
                                {{ __('ui.narrator.action_enchant', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'sniff')
                                {{ __('ui.narrator.action_sniff', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'link_lovers')
                                {{ __('ui.narrator.action_love', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'choose_side')
                                {{ __('ui.narrator.action_choose_side') }}
                            @elseif($action['action_type'] === 'extra_kill')
                                {{ __('ui.narrator.action_extra_kill', ['name' => $action['target_nickname'] ?? '?']) }}
                            @elseif($action['action_type'] === 'solo_kill')
                                {{ __('ui.narrator.action_solo_kill', ['name' => $action['target_nickname'] ?? '?']) }}
                            @else
                                {{ __('ui.narrator.action_generic', ['type' => $action['action_type'], 'name' => $action['target_nickname'] ?? '?']) }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-6">
                <p class="text-text-muted text-xs">{{ __('ui.narrator.no_actions_yet') }}</p>
                @if(count($actions) === 0)
                    <div class="flex justify-center gap-1 mt-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent-blue/40 animate-pulse animation-delay-200"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-accent-blue/40 animate-pulse animation-delay-400"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-accent-blue/40 animate-pulse animation-delay-600"></span>
                    </div>
                @endif
            </div>
        @endforelse
    </div>
</div>
