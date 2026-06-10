@props([
    'winningFaction' => 'no_one',
    'players' => collect(),
    'showNewGame' => false,
    'onNewGame' => null,
    'onReturnLobby' => null,
    'currentPlayerId' => null,
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

$currentPlayer = $currentPlayerId ? $players->firstWhere('id', $currentPlayerId) : null;
$playerFaction = $currentPlayer?->role?->faction;
$isWinner = $playerFaction && ($winningFaction === $playerFaction || ($winningFaction === 'lovers' && in_array($playerFaction, ['village', 'werewolves'])));
// Lovers win: both lovers alive and cross-faction — overrides normal win condition

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

<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
     x-data="{ show: false, particles: [] }"
     x-init="
         setTimeout(() => show = true, 100);
         // Generate CSS particle positions
         particles = Array.from({length: 20}, (_, i) => ({
             left: (Math.random() * 100) + '%',
             delay: (Math.random() * 3) + 's',
             duration: (2 + Math.random() * 3) + 's',
             size: (4 + Math.random() * 8) + 'px',
         }));
     "
     x-show="show" x-transition:enter="transition-all duration-500"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    {{-- Floating particles --}}
    <template x-for="(p, i) in particles" :key="i">
        <div class="absolute rounded-full pointer-events-none"
             :style="{
                 left: p.left,
                 bottom: '-10px',
                 width: p.size,
                 height: p.size,
                 animation: 'particleFloat ' + p.duration + ' ease-out ' + p.delay + ' infinite',
                 background: 'radial-gradient(circle, rgba(251,191,36,0.4) 0%, transparent 70%)',
             }">
        </div>
    </template>

    <div class="w-full max-w-lg" x-show="show" x-transition:enter="transition-all duration-500"
         x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
         style="animation-delay: 150ms;">
        <div class="glass-panel border-2 {{ $fc['border'] }} overflow-hidden {{ $fc['glow'] }} victory-glow">
            {{-- Winner header --}}
            <div class="bg-gradient-to-b {{ $fc['gradient'] }} to-transparent p-8 md:p-10 text-center relative">
                <div class="absolute inset-0 bg-gradient-to-t from-transparent to-white/5 pointer-events-none"></div>
                <div class="text-6xl md:text-7xl mb-4 animate-floatSlow">{{ $fc['icon'] }}</div>
                <h2 class="text-3xl md:text-4xl font-serif font-bold {{ $fc['color'] }} animate-fadeInScale">{{ __('ui.game.over') }}</h2>
                <div class="w-16 h-0.5 bg-accent-gold/30 mx-auto my-3"></div>
                <p class="text-xl md:text-2xl {{ $fc['color'] }} font-semibold animate-slideUpReveal" style="animation-delay: 200ms;">{{ $fc['label'] }}</p>
            </div>

            <div class="p-5 md:p-6 space-y-4">
                @if($currentPlayer)
                    <div class="text-center animate-slideUpReveal" style="animation-delay: 250ms;">
                        <p class="text-lg font-bold {{ $isWinner ? 'text-accent-green' : 'text-text-muted' }}">
                            {{ $isWinner ? __('ui.win.you_win') : __('ui.win.you_lose') }}
                        </p>
                        <p class="text-xs text-text-muted mt-1">
                            {{ __('ui.win.you_were') }}
                            <span class="text-accent-gold font-semibold">{{ $currentPlayer->role ? __("roles.{$currentPlayer->role->key}.name") : '?' }}</span>
                        </p>
                    </div>
                @endif
                @if($alivePlayers->isNotEmpty())
                    <div class="animate-slideUpReveal" style="animation-delay: 300ms;">
                        <h4 class="text-xs uppercase tracking-wider text-accent-green font-semibold mb-3 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-accent-green"></span>
                            {{ __('ui.game.survivors') }}
                        </h4>
                        <div class="space-y-1.5">
                            @foreach($alivePlayers as $p)
                                @php $alc = $factionColors[$p->role?->faction ?? ''] ?? 'border-l-border-default'; @endphp
                                <div class="flex items-center justify-between px-3 py-2 bg-accent-green/5 rounded-lg border border-accent-green/20 border-l-2 {{ $alc }} animate-slideUpReveal"
                                     style="animation-delay: {{ 350 + $loop->index * 60 }}ms;">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-accent-green/20 flex items-center justify-center text-sm font-bold text-accent-green">
                                            {{ strtoupper(substr($p->nickname, 0, 1)) }}
                                        </div>
                                        <span class="text-text-primary text-sm font-medium">{{ $p->nickname }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <x-role-icon :roleKey="$p->role?->key ?? ''" class="text-sm" />
                                        <span class="text-text-muted text-xs">{{ $p->role ? __("roles.{$p->role->key}.name") : '?' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($deadPlayers->isNotEmpty())
                    <div class="animate-slideUpReveal" style="animation-delay: 450ms;">
                        <h4 class="text-xs uppercase tracking-wider text-accent-red font-semibold mb-3 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-accent-red"></span>
                            {{ __('ui.game.eliminated') }}
                        </h4>
                        <div class="space-y-1.5">
                            @foreach($deadPlayers as $p)
                                @php $dlc = $factionColors[$p->role?->faction ?? ''] ?? 'border-l-border-default'; @endphp
                                <div class="flex items-center justify-between px-3 py-2 bg-accent-red/5 rounded-lg border border-accent-red/20 border-l-2 {{ $dlc }} opacity-70 animate-slideUpReveal"
                                     style="animation-delay: {{ 500 + $loop->index * 60 }}ms;">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-accent-red/20 flex items-center justify-center text-sm font-bold text-accent-red">
                                            {{ strtoupper(substr($p->nickname, 0, 1)) }}
                                        </div>
                                        <span class="text-text-primary text-sm line-through">{{ $p->nickname }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <x-role-icon :roleKey="$p->role?->key ?? ''" class="text-sm" />
                                        <span class="text-text-muted text-xs">{{ $p->role ? __("roles.{$p->role->key}.name") : '?' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($showNewGame)
                    <div class="pt-5 border-t border-border-default flex flex-col gap-3 animate-slideUpReveal" style="animation-delay: 700ms;">
                        @if($onNewGame)
                            <button wire:click="{{ $onNewGame }}"
                                    wire:confirm="{{ __('ui.narrator.confirm_new_game') }}"
                                    class="w-full py-3 px-6 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm hover:scale-[1.03] active:scale-95 shadow-lg">
                                {{ __('ui.narrator.new_game') }}
                            </button>
                        @endif
                        @if($onReturnLobby)
                            <button wire:click="{{ $onReturnLobby }}"
                                    class="w-full py-3 px-6 border border-border-default text-text-secondary font-medium rounded-lg hover:bg-bg-surface transition-all duration-200 text-sm hover:scale-[1.03] active:scale-95">
                                {{ __('ui.button.return_lobby') }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
