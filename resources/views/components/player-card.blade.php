@props([
    'player' => null,
    'isAlive' => true,
    'isLover' => false,
    'isEnchanted' => false,
    'isSelected' => false,
    'isCurrentAction' => false,
    'showRole' => false,
    'size' => 'normal',
])

@php
$roleKey = $player?->role?->key ?? '';
$initials = $player ? strtoupper(substr($player->nickname, 0, 2)) : '??';
$isDead = !$isAlive;
$sizeClass = $size === 'small' ? 'p-2.5' : 'p-3';
@endphp

<div class="relative group {{ $isDead ? 'player-card-dead' : 'player-card-alive' }}
    {{ $isSelected ? 'player-card-selected' : '' }}
    {{ $isCurrentAction ? 'player-card-current' : '' }}
    {{ $sizeClass }}"
    {{ $attributes }}>
    <div class="flex items-center gap-2.5">
        <div class="relative flex-shrink-0">
            <div class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-bg-elevated flex items-center justify-center text-sm font-bold text-text-secondary">
                {{ $initials }}
            </div>
            @if($isDead)
                <div class="absolute -top-0.5 -end-0.5 w-4 h-4 bg-accent-red rounded-full flex items-center justify-center text-[10px]">💀</div>
            @elseif($isLover)
                <div class="absolute -top-0.5 -end-0.5 w-3.5 h-3.5 bg-accent-pink rounded-full flex items-center justify-center text-[8px]">💕</div>
            @elseif($isEnchanted)
                <div class="absolute -top-0.5 -end-0.5 w-3.5 h-3.5 bg-accent-green rounded-full flex items-center justify-center text-[8px]">✦</div>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
                <p class="text-text-primary text-sm font-medium truncate {{ $isDead ? 'line-through text-text-muted' : '' }}">
                    {{ $player->nickname }}
                </p>
                @if($showRole && $roleKey)
                    <x-role-icon :roleKey="$roleKey" class="text-sm flex-shrink-0" />
                @endif
            </div>
            @if($showRole && $roleKey)
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-text-secondary text-[10px] truncate">{{ __("roles.{$roleKey}.name") }}</span>
                </div>
            @else
                <p class="text-text-muted text-xs truncate">
                    @if($isDead)
                        {{ __('ui.game.dead') }}
                    @elseif($isLover)
                        {{ __('ui.game.lover_short') }}
                    @elseif($isEnchanted)
                        {{ __('ui.game.enchanted_short') }}
                    @else
                        {{ $isSelected ? __('ui.game.selected') : '' }}
                    @endif
                </p>
            @endif
        </div>
    </div>
</div>
