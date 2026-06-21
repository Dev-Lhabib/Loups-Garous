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

<header x-data="{ mobileOpen: false }"
        class="sticky top-0 z-40 w-full border-b border-border-default/50 bg-bg-primary/80 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-3 md:px-6" dir="{{ $dir }}">
        <div class="flex items-center justify-between h-12 md:h-14 gap-1 md:gap-2">

            {{-- Left: Brand --}}
            <div class="flex items-center gap-2 min-w-0 flex-shrink-0">
                <a href="{{ route('home') }}"
                   class="flex items-center gap-2 text-sm md:text-base font-serif font-bold text-accent-gold hover:text-accent-gold-dark transition-colors whitespace-nowrap">
                    <span class="text-base md:text-lg">🐺</span>
                    <span class="hidden sm:inline">{{ __('ui.app.name') }}</span>
                    <span class="sm:hidden">{{ __('ui.app.name') }}</span>
                </a>
            </div>

            {{-- Center: Nav links (home) or game context (game pages) --}}
            <div class="hidden md:flex items-center justify-center flex-1 min-w-0 px-2">
                @if($isHomePage || $isCreatePage || $isJoinPage)
                    <nav class="flex items-center gap-1" aria-label="Main navigation">
                        <a href="{{ route('home') }}#story"
                           class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text-primary hover:bg-bg-surface rounded-lg transition-colors duration-200">
                            {{ __('ui.home.nav_story') }}
                        </a>
                        <a href="{{ route('home') }}#how-it-works"
                           class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text-primary hover:bg-bg-surface rounded-lg transition-colors duration-200">
                            {{ __('ui.home.nav_how_to_play') }}
                        </a>
                        <a href="{{ route('home') }}#factions"
                           class="px-3 py-1.5 text-xs font-medium text-text-muted hover:text-text-primary hover:bg-bg-surface rounded-lg transition-colors duration-200">
                            {{ __('ui.home.nav_roles') }}
                        </a>
                    </nav>
                @elseif($isGamePage && $phase)
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
                @elseif(($room) && ($isLobbyPage || $isGamePage))
                    <div class="flex items-center gap-2 md:gap-3 text-xs md:text-sm">
                        <span class="text-text-muted text-[10px] md:text-xs uppercase tracking-wider hidden sm:inline">{{ __('ui.lobby.room_code') }}:</span>
                        <span class="font-mono text-accent-gold font-bold tracking-wider text-sm md:text-base">{{ $room->code }}</span>
                    </div>
                @endif
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-1 flex-shrink-0">

                {{-- CTA buttons (desktop, home page only) --}}
                @if($isHomePage || $isCreatePage || $isJoinPage)
                    <a href="{{ route('rooms.create') }}"
                       class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-accent-gold text-bg-primary font-semibold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-xs shadow-sm shadow-accent-gold/20">
                        {{ __('ui.home.hero_start') }}
                    </a>
                    <a href="{{ route('rooms.join') }}"
                       class="hidden md:inline-flex items-center gap-1.5 px-3.5 py-1.5 border border-accent-gold/40 text-accent-gold font-semibold rounded-lg hover:bg-accent-gold hover:text-bg-primary transition-all duration-200 text-xs">
                        {{ __('ui.home.hero_join') }}
                    </a>
                @endif

                {{-- Theme toggle --}}
                <x-theme-toggle />

                {{-- Language switcher --}}
                <x-language-switcher />

                {{-- Mobile hamburger --}}
                <button @click="mobileOpen = !mobileOpen"
                        class="md:hidden flex items-center justify-center w-9 h-9 rounded-lg text-text-muted hover:text-text-primary hover:bg-bg-surface border border-transparent hover:border-border-default transition-all duration-200"
                        aria-label="{{ __('ui.nav.toggle') }}"
                        :aria-expanded="mobileOpen">
                    <svg x-show="!mobileOpen" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu overlay --}}
    <div x-show="mobileOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-[-4px]"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-[-4px]"
         @click.away="mobileOpen = false"
         class="md:hidden border-t border-border-default/50 bg-bg-primary/95 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-3 py-4 space-y-3">

            @if($isHomePage || $isCreatePage || $isJoinPage)
                {{-- Nav links (mobile) --}}
                <nav class="space-y-1" aria-label="Mobile navigation">
                    <a href="{{ route('home') }}#story" @click="mobileOpen = false"
                       class="block px-3 py-2.5 text-sm font-medium text-text-secondary hover:text-text-primary hover:bg-bg-surface rounded-lg transition-colors">
                        {{ __('ui.home.nav_story') }}
                    </a>
                    <a href="{{ route('home') }}#how-it-works" @click="mobileOpen = false"
                       class="block px-3 py-2.5 text-sm font-medium text-text-secondary hover:text-text-primary hover:bg-bg-surface rounded-lg transition-colors">
                        {{ __('ui.home.nav_how_to_play') }}
                    </a>
                    <a href="{{ route('home') }}#factions" @click="mobileOpen = false"
                       class="block px-3 py-2.5 text-sm font-medium text-text-secondary hover:text-text-primary hover:bg-bg-surface rounded-lg transition-colors">
                        {{ __('ui.home.nav_roles') }}
                    </a>
                </nav>

                {{-- CTA buttons (mobile) --}}
                <div class="pt-2 space-y-2">
                    <a href="{{ route('rooms.create') }}" @click="mobileOpen = false"
                       class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 text-sm">
                        {{ __('ui.home.hero_start') }}
                    </a>
                    <a href="{{ route('rooms.join') }}" @click="mobileOpen = false"
                       class="flex items-center justify-center gap-2 w-full px-4 py-2.5 border border-accent-gold/40 text-accent-gold font-bold rounded-xl hover:bg-accent-gold hover:text-bg-primary transition-all duration-200 text-sm">
                        {{ __('ui.home.hero_join') }}
                    </a>
                </div>
            @endif

            <p class="text-[10px] text-text-muted/40 text-center pt-1">{{ __('ui.app.name') }}</p>
        </div>
    </div>
</header>
