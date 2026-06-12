@props([
    'room' => null,
    'phase' => null,
    'round' => null,
    'aliveCount' => null,
    'totalCount' => null,
])

@php
    $routeName = request()->route()?->getName();
    $isGamePage = str_starts_with($routeName ?? '', 'game.');
    $isLobbyPage = str_starts_with($routeName ?? '', 'lobby.');
    $isHomePage = $routeName === 'home';
    $isCreatePage = $routeName === 'rooms.create';
    $isJoinPage = $routeName === 'rooms.join';

    $showCenter = $room || ($isGamePage && $phase) || ($isLobbyPage && $room);
    $locale = app()->getLocale();
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
@endphp

<header class="sticky top-0 z-40 w-full border-b border-border-default/50 bg-bg-primary/80 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-3 md:px-6" dir="{{ $dir }}">
        <div class="flex items-center justify-between h-12 md:h-14 gap-2 md:gap-4">

            {{-- Left: Brand --}}
            <div class="flex items-center gap-2 min-w-0 flex-shrink-0">
                <a href="{{ route('home') }}"
                   class="flex items-center gap-2 text-sm md:text-base font-serif font-bold text-accent-gold hover:text-accent-gold-dark transition-colors whitespace-nowrap">
                    <span class="text-base md:text-lg">🐺</span>
                    <span class="hidden sm:inline">{{ __('ui.app.name') }}</span>
                    <span class="sm:hidden">{{ __('ui.app.name') }}</span>
                </a>
            </div>

            {{-- Center: Contextual info --}}
            <div class="flex-1 flex items-center justify-center min-w-0 px-2">
                @if($isGamePage && $phase)
                    <div class="flex items-center gap-2 md:gap-3 text-xs md:text-sm">
                        <span class="text-lg md:text-xl">
                            @switch($phase)
                                @case('night') 🌙 @break
                                @case('day') ☀️ @break
                                @case('voting') 🗳️ @break
                                @case('finished') 🏆 @break
                                @default ⏳
                            @endswitch
                        </span>
                        <span class="text-text-primary font-medium truncate">
                            @switch($phase)
                                @case('night') {{ __('ui.phase.night') }} @break
                                @case('day') {{ __('ui.phase.day') }} @break
                                @case('voting') {{ __('ui.phase.voting') }} @break
                                @case('finished') {{ __('ui.phase.finished') }} @break
                                @default {{ __('ui.phase.waiting') }}
                            @endswitch
                        </span>
                        @if($round)
                            <span class="text-text-muted text-[10px] md:text-xs font-mono bg-bg-surface px-1.5 py-0.5 rounded">{{ __('ui.game.round_short', ['number' => $round]) }}</span>
                        @endif
                        @if($aliveCount !== null && $totalCount !== null)
                            <span class="text-text-muted text-[10px] md:text-xs hidden sm:inline">·</span>
                            <span class="text-text-muted text-[10px] md:text-xs hidden sm:inline">{{ $aliveCount }}/{{ $totalCount }} {{ __('ui.game.players_alive') }}</span>
                        @endif
                    </div>
                @elseif($room && ($isLobbyPage || $isGamePage))
                    <div class="flex items-center gap-2 md:gap-3 text-xs md:text-sm">
                        <span class="text-text-muted text-[10px] md:text-xs uppercase tracking-wider hidden sm:inline">{{ __('ui.lobby.room_code') }}:</span>
                        <span class="font-mono text-accent-gold font-bold tracking-wider text-sm md:text-base">{{ $room->code }}</span>
                    </div>
                @endif
            </div>

            {{-- Right: Language Switcher --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                <x-language-switcher />
            </div>

        </div>
    </div>
</header>
