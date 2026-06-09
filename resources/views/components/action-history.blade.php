@props([
    'actions' => [],
])

@php
$actionIcons = [
    'kill' => '🐺', 'extra_kill' => '🐾', 'convert' => '🦇', 'solo_kill' => '🌕',
    'protect' => '🛡️', 'inspect' => '👁️', 'save' => '🧪', 'poison' => '☠️',
    'enchant' => '🎵', 'sniff' => '🦊', 'link_lovers' => '💘', 'choose_side' => '🐕',
];

$grouped = collect($actions)->groupBy('round')->sortKeysDesc();
$actionLangMap = [
    'kill' => 'action_kill', 'extra_kill' => 'action_extra_kill', 'convert' => 'action_convert',
    'solo_kill' => 'action_solo_kill', 'protect' => 'action_protect', 'save' => 'action_save',
    'poison' => 'action_poison', 'inspect' => 'action_inspect', 'enchant' => 'action_enchant',
    'sniff' => 'action_sniff', 'link_lovers' => 'action_love', 'choose_side' => 'action_choose_side',
];
@endphp

<div class="glass-panel border border-border-default p-4"
     x-data="{ expandedRound: null }">
    <h3 class="text-text-primary text-sm font-semibold mb-3 flex items-center gap-2">
        <span>📋</span>
        <span>{{ __('ui.narrator.action_history') }}</span>
    </h3>
    <div class="space-y-2 max-h-96 overflow-y-auto scrollbar-thin">
        @forelse($grouped as $round => $roundActions)
            <div class="rounded-lg border border-border-default overflow-hidden">
                <button @click="expandedRound = expandedRound === {{ $round }} ? null : {{ $round }}"
                        class="w-full flex items-center justify-between px-3 py-2 bg-bg-elevated/50 hover:bg-bg-elevated transition-colors text-xs font-medium text-text-primary">
                    <span>{{ __('ui.game.round', ['number' => $round]) }}</span>
                    <span class="text-text-muted transition-transform duration-200"
                          :class="expandedRound === {{ $round }} ? 'rotate-180' : ''">
                        ▼
                    </span>
                </button>
                <div x-show="expandedRound === {{ $round }}" x-collapse.duration.200ms>
                    <div class="space-y-1 p-2">
                        @foreach($roundActions as $action)
                            @php
                                $icon = $actionIcons[$action['action_type']] ?? '❓';
                                $langKey = $actionLangMap[$action['action_type']] ?? 'action_generic';
                                $roleName = isset($action['role_key']) ? __("roles.{$action['role_key']}.name") : '';
                                $playerName = $action['player_nickname'] ?? '';
                                $targetName = $action['target_nickname'] ?? '?';
                            @endphp
                            <div class="flex items-center gap-2 px-2 py-1.5 rounded bg-bg-surface/30 text-xs">
                                <span>{{ $icon }}</span>
                                <span class="text-text-secondary truncate">{{ $playerName }}</span>
                                <span class="text-text-muted">({{ $roleName }})</span>
                                <span class="text-text-primary ml-auto truncate">
                                    @if($langKey === 'action_generic')
                                        {{ __('ui.narrator.action_generic', ['type' => $action['action_type'], 'name' => $targetName]) }}
                                    @else
                                        {{ __("ui.narrator.{$langKey}", ['name' => $targetName]) }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <p class="text-text-muted text-xs text-center py-6 italic">{{ __('ui.narrator.no_actions_yet') }}</p>
        @endforelse
    </div>
</div>
