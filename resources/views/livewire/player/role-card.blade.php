@php
$factionColors = [
    'village' => ['border' => 'border-accent-blue', 'glow' => 'glow-blue', 'text' => 'text-accent-blue', 'bg' => 'from-accent-blue/20 to-accent-blue/5'],
    'werewolves' => ['border' => 'border-accent-red', 'glow' => 'glow-red', 'text' => 'text-accent-red', 'bg' => 'from-accent-red/20 to-accent-red/5'],
    'white_werewolf' => ['border' => 'border-accent-purple', 'glow' => '', 'text' => 'text-accent-purple', 'bg' => 'from-accent-purple/20 to-accent-purple/5'],
    'pied_piper' => ['border' => 'border-accent-green', 'glow' => 'glow-green', 'text' => 'text-accent-green', 'bg' => 'from-accent-green/20 to-accent-green/5'],
    'angel' => ['border' => 'border-accent-gold', 'glow' => 'glow-gold', 'text' => 'text-accent-gold', 'bg' => 'from-accent-gold/20 to-accent-gold/5'],
    'lovers' => ['border' => 'border-accent-pink', 'glow' => '', 'text' => 'text-accent-pink', 'bg' => 'from-accent-pink/20 to-accent-pink/5'],
];

$fc = $factionColors[$role->faction] ?? $factionColors['village'];
@endphp

<div class="relative w-72 h-[22rem] rounded-xl cursor-pointer select-none"
     x-data="{ revealed: false }"
     x-on:mousedown="revealed = true"
     x-on:mouseup="revealed = false"
     x-on:mouseleave="revealed = false"
     x-on:touchstart="revealed = true"
     x-on:touchend="revealed = false">

    {{-- Masked face --}}
    <div x-show="!revealed"
         class="absolute inset-0 card-masked rounded-xl flex items-center justify-center">
        <div class="text-center space-y-3">
            <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-accent-gold/20 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center">
                <div class="text-4xl text-accent-gold/50 font-serif font-bold">?</div>
            </div>
            <p class="text-text-muted text-sm tracking-widest uppercase font-medium">{{ __('ui.role.hold_to_reveal') }}</p>
            <p class="text-text-muted/50 text-xs">{{ $player->nickname }}</p>
        </div>
    </div>

    {{-- Revealed face --}}
    <div x-show="revealed"
         x-cloak
         class="absolute inset-0 card-revealed {{ $fc['glow'] }} rounded-xl p-6 flex flex-col items-center justify-center">
        <div class="flex-1 flex flex-col items-center justify-center space-y-3">
            <span class="text-xs uppercase tracking-widest {{ $fc['text'] }} font-medium">{{ __("ui.factions.{$role->faction}") }}</span>
            <x-role-icon :roleKey="$role->key" class="text-4xl" />
            <h2 class="text-2xl font-serif font-bold text-text-primary text-center">{{ __("roles.{$role->key}.name") }}</h2>
            <p class="text-text-muted text-sm text-center max-w-[16rem] leading-relaxed">{{ __("roles.{$role->key}.description") }}</p>
            @if($role->night_order !== null)
                <div class="mt-2 {{ $fc['text'] }} text-xs bg-bg-elevated/50 px-3 py-1 rounded-full">
                    {{ __('ui.role.night_order') }}: {{ $role->night_order }}
                </div>
            @endif
        </div>
    </div>
</div>
