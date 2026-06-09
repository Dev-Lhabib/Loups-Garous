@props([
    'winningFaction' => 'no_one',
    'players' => collect(),
    'showNewGame' => false,
    'onNewGame' => null,
    'onReturnLobby' => null,
])

@php
$factionConfig = [
    'village' => [
        'icon' => '🏘️', 'label' => __('ui.win.village'),
        'color' => 'text-accent-blue', 'bg' => 'bg-accent-blue/10',
        'border' => 'border-accent-blue/40', 'glow' => 'glow-blue',
        'gradient' => 'from-accent-blue/20',
        'animation' => 'animate-fadeInUp',
    ],
    'werewolves' => [
        'icon' => '🐺', 'label' => __('ui.win.werewolves'),
        'color' => 'text-accent-red', 'bg' => 'bg-accent-red/10',
        'border' => 'border-accent-red/40', 'glow' => 'glow-red',
        'gradient' => 'from-accent-red/20',
        'animation' => 'animate-fadeInUp',
    ],
    'white_werewolf' => [
        'icon' => '🌕', 'label' => __('ui.win.white_werewolf'),
        'color' => 'text-accent-purple', 'bg' => 'bg-accent-purple/10',
        'border' => 'border-accent-purple/40', 'glow' => '',
        'gradient' => 'from-accent-purple/20',
        'animation' => 'animate-fadeInUp',
    ],
    'pied_piper' => [
        'icon' => '🎵', 'label' => __('ui.win.pied_piper'),
        'color' => 'text-accent-green', 'bg' => 'bg-accent-green/10',
        'border' => 'border-accent-green/40', 'glow' => 'glow-green',
        'gradient' => 'from-accent-green/20',
        'animation' => 'animate-fadeInUp',
    ],
    'angel' => [
        'icon' => '😇', 'label' => __('ui.win.angel'),
        'color' => 'text-accent-gold', 'bg' => 'bg-accent-gold/10',
        'border' => 'border-accent-gold/40', 'glow' => 'glow-gold',
        'gradient' => 'from-accent-gold/20',
        'animation' => 'animate-fadeInUp',
    ],
    'lovers' => [
        'icon' => '💕', 'label' => __('ui.win.lovers'),
        'color' => 'text-accent-pink', 'bg' => 'bg-accent-pink/10',
        'border' => 'border-accent-pink/40', 'glow' => '',
        'gradient' => 'from-accent-pink/20',
        'animation' => 'animate-fadeInUp',
    ],
    'no_one' => [
        'icon' => '💀', 'label' => __('ui.win.no_one'),
        'color' => 'text-text-muted', 'bg' => 'bg-bg-surface',
        'border' => 'border-border-default', 'glow' => '',
        'gradient' => 'from-bg-surface',
        'animation' => 'animate-fadeInUp',
    ],
];

$fc = $factionConfig[$winningFaction] ?? $factionConfig['no_one'];

$alivePlayers = $players->filter(fn($p) => $p->is_alive);
$deadPlayers = $players->filter(fn($p) => !$p->is_alive);

$factionColors = [
    'village' => 'border-l-accent-blue',
    'werewolves' => 'border-l-accent-red',
    'white_werewolf' => 'border-l-accent-purple',
    'pied_piper' => 'border-l-accent-green',
    'angel' => 'border-l-accent-gold',
    'lovers' => 'border-l-accent-pink',
];
@endphp

<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
     x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)"
     x-show="show" x-transition:enter="transition-all duration-500"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="w-full max-w-lg {{ $fc['animation'] }}" x-show="show" x-transition:enter="transition-all duration-500"
         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         style="animation-delay: 150ms;">
        <div class="glass-panel border-2 {{ $fc['border'] }} overflow-hidden {{ $fc['glow'] }}">
            <div class="bg-gradient-to-b {{ $fc['gradient'] }} to-transparent p-8 text-center">
                <div class="text-6xl mb-3 animate-heartbeat">{{ $fc['icon'] }}</div>
                <h2 class="text-3xl font-serif font-bold {{ $fc['color'] }}">{{ __('ui.game.over') }}</h2>
                <p class="text-xl mt-2 {{ $fc['color'] }} font-semibold">{{ $fc['label'] }}</p>
            </div>

            <div class="p-6 space-y-4">
                @if($alivePlayers->isNotEmpty())
                    <div class="animate-fadeInUp" style="animation-delay: 300ms;">
                        <h4 class="text-xs uppercase tracking-wider text-accent-green font-semibold mb-2 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-accent-green"></span>
                            {{ __('ui.game.survivors') }}
                        </h4>
                        <div class="space-y-1.5">
                            @foreach($alivePlayers as $p)
                                @php $alc = $factionColors[$p->role?->faction ?? ''] ?? 'border-l-border-default'; @endphp
                                <div class="flex items-center justify-between px-3 py-2 bg-accent-green/5 rounded-lg border border-accent-green/20 border-l-2 {{ $alc }}">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-accent-green/20 flex items-center justify-center text-xs font-bold text-accent-green">
                                            {{ strtoupper(substr($p->nickname, 0, 1)) }}
                                        </div>
                                        <span class="text-text-primary text-sm font-medium">{{ $p->nickname }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <x-role-icon :roleKey="$p->role?->key ?? ''" class="text-xs" />
                                        <span class="text-text-muted text-xs">{{ $p->role ? __("roles.{$p->role->key}.name") : '?' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($deadPlayers->isNotEmpty())
                    <div class="animate-fadeInUp" style="animation-delay: 450ms;">
                        <h4 class="text-xs uppercase tracking-wider text-accent-red font-semibold mb-2 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-accent-red"></span>
                            {{ __('ui.game.eliminated') }}
                        </h4>
                        <div class="space-y-1.5">
                            @foreach($deadPlayers as $p)
                                @php $dlc = $factionColors[$p->role?->faction ?? ''] ?? 'border-l-border-default'; @endphp
                                <div class="flex items-center justify-between px-3 py-2 bg-accent-red/5 rounded-lg border border-accent-red/20 border-l-2 {{ $dlc }} opacity-70">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-accent-red/20 flex items-center justify-center text-xs font-bold text-accent-red">
                                            {{ strtoupper(substr($p->nickname, 0, 1)) }}
                                        </div>
                                        <span class="text-text-primary text-sm line-through">{{ $p->nickname }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <x-role-icon :roleKey="$p->role?->key ?? ''" class="text-xs" />
                                        <span class="text-text-muted text-xs">{{ $p->role ? __("roles.{$p->role->key}.name") : '?' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($showNewGame)
                    <div class="pt-4 border-t border-border-default flex flex-col gap-3 animate-fadeInUp" style="animation-delay: 600ms;">
                        @if($onNewGame)
                            <button wire:click="{{ $onNewGame }}"
                                    wire:confirm="{{ __('ui.narrator.confirm_new_game') }}"
                                    class="w-full py-3 px-6 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm hover:scale-[1.02] active:scale-95 shadow-lg">
                                {{ __('ui.narrator.new_game') }}
                            </button>
                        @endif
                        @if($onReturnLobby)
                            <button wire:click="{{ $onReturnLobby }}"
                                    class="w-full py-3 px-6 border border-border-default text-text-secondary font-medium rounded-lg hover:bg-bg-surface transition-all duration-200 text-sm hover:scale-[1.02] active:scale-95">
                                {{ __('ui.button.return_lobby') }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
