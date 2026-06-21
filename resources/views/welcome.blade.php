@extends('layouts.app')

@section('content')
<div class="relative z-10">

    {{-- ==================== HERO ==================== --}}
    <section id="hero" class="relative min-h-screen flex flex-col items-center justify-center px-4 md:px-6 pt-14 sm:pt-16 pb-10 sm:pb-12 overflow-hidden">
        {{-- Animated particles --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            @for ($i = 0; $i < 20; $i++)
                @php
                    $size = rand(2, 6);
                    $left = rand(0, 100);
                    $delay = rand(0, 15);
                    $duration = rand(8, 20);
                    $opacity = rand(1, 4) * 0.01;
                @endphp
                <div class="absolute rounded-full bg-accent-gold"
                     style="width: {{ $size }}px; height: {{ $size }}px; left: {{ $left }}%; top: {{ rand(0, 100) }}%; opacity: {{ $opacity }}; animation: particleFloat {{ $duration }}s ease-in-out {{ $delay }}s infinite;"></div>
            @endfor
        </div>

        <div class="relative z-10 w-full max-w-3xl mx-auto text-center space-y-8 sm:space-y-10 md:space-y-14">

            {{-- Moon & Wolf --}}
            <div class="animate-fadeInUp">
                <div class="relative w-20 h-20 sm:w-24 sm:h-24 md:w-32 md:h-32 mx-auto">
                    <div class="absolute inset-0 rounded-full bg-accent-gold/10 animate-pulse"
                         style="box-shadow: 0 0 60px rgba(251, 191, 36, 0.15), 0 0 120px rgba(251, 191, 36, 0.05);"></div>
                    <div class="absolute inset-2 rounded-full bg-gradient-to-br from-accent-gold/20 to-accent-gold/5 border border-accent-gold/30 flex items-center justify-center animate-floatSlow">
                        <span class="text-4xl md:text-5xl">🐺</span>
                    </div>
                </div>
            </div>

            {{-- Title --}}
            <div class="space-y-4 md:space-y-6 animate-fadeInUp animation-delay-200">
                <h1 class="font-serif text-4xl md:text-6xl lg:text-7xl text-text-primary font-bold tracking-wide leading-tight">
                    {{ __('ui.app.name') }}
                </h1>
                <p class="font-serif text-xl md:text-2xl lg:text-3xl text-accent-gold font-semibold animate-bannerPulse">
                    {{ __('ui.home.hero_title') }}
                </p>
            </div>

            {{-- Subtitle --}}
            <p class="text-text-secondary text-sm md:text-base lg:text-lg max-w-2xl mx-auto leading-relaxed px-2 animate-fadeInUp animation-delay-300">
                {{ __('ui.home.hero_subtitle') }}
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 md:gap-4 animate-fadeInUp animation-delay-400">
                <a href="{{ route('rooms.create') }}"
                   class="group relative w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 md:px-10 py-3.5 md:py-4 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 hover:scale-[1.03] active:scale-95 shadow-lg shadow-accent-gold/20 text-sm md:text-base">
                    <span>{{ __('ui.home.hero_start') }}</span>
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('rooms.join') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 md:px-10 py-3.5 md:py-4 border-2 border-accent-gold/40 text-accent-gold font-bold rounded-xl hover:bg-accent-gold hover:text-bg-primary transition-all duration-200 hover:scale-[1.03] active:scale-95 text-sm md:text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>{{ __('ui.home.hero_join') }}</span>
                </a>
            </div>

        </div>
    </section>

    {{-- ==================== STORY ==================== --}}
    <section id="story" class="relative py-20 md:py-28 px-4 md:px-6 scroll-mt-16">
        <div class="max-w-5xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 md:gap-16 items-center">

                {{-- Visual --}}
                <div class="relative animate-fadeInUp">
                    <div class="relative aspect-[4/3] max-w-md mx-auto w-full">
                        <div class="absolute inset-0 bg-gradient-to-br from-accent-gold/10 to-accent-purple/10 rounded-3xl border border-border-default"></div>
                        <div class="absolute inset-4 bg-gradient-to-br from-accent-gold/5 to-accent-purple/5 rounded-2xl border border-border-muted flex flex-col items-center justify-center p-6 text-center space-y-4">
                            <div class="text-6xl md:text-7xl animate-floatSlow">🌙</div>
                            <p class="font-serif text-base md:text-lg text-text-secondary italic leading-relaxed max-w-xs">
                                "{{ __('ui.home.hero_title') }}"
                            </p>
                            <div class="flex gap-2 text-lg">
                                <span>🏘️</span>
                                <span>🐺</span>
                                <span>🌲</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Text --}}
                <div class="space-y-5 animate-fadeInUp animation-delay-200">
                    <h2 class="font-serif text-3xl md:text-4xl text-text-primary font-bold">
                        {{ __('ui.home.story_title') }}
                    </h2>
                    <div class="space-y-4 text-text-secondary text-sm md:text-base leading-relaxed">
                        <p>{{ __('ui.home.story_p1') }}</p>
                        <p>{{ __('ui.home.story_p2') }}</p>
                    </div>
                    <p class="font-serif text-accent-gold font-medium text-base md:text-lg italic">
                        {{ __('ui.home.story_p3') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Divider --}}
    <div class="max-w-3xl mx-auto px-4">
        <div class="h-px bg-gradient-to-r from-transparent via-border-default to-transparent"></div>
    </div>

    {{-- ==================== HOW IT WORKS ==================== --}}
    <section id="how-it-works" class="relative py-20 md:py-28 px-4 md:px-6 scroll-mt-16">
        <div class="max-w-5xl mx-auto">
            {{-- Section header --}}
            <div class="text-center mb-14 md:mb-18 space-y-4">
                <h2 class="font-serif text-3xl md:text-4xl text-text-primary font-bold">
                    {{ __('ui.home.how_it_works_title') }}
                </h2>
                <div class="w-16 h-0.5 bg-accent-gold/50 mx-auto rounded-full"></div>
            </div>

            {{-- Steps --}}
            @php
                $steps = [
                    ['icon' => '🎮', 'color' => 'from-accent-blue/20 to-accent-blue/5', 'border' => 'border-accent-blue/30'],
                    ['icon' => '🎭', 'color' => 'from-accent-purple/20 to-accent-purple/5', 'border' => 'border-accent-purple/30'],
                    ['icon' => '🌙', 'color' => 'from-accent-gold/20 to-accent-gold/5', 'border' => 'border-accent-gold/30'],
                    ['icon' => '☀️', 'color' => 'from-accent-amber/20 to-accent-amber/5', 'border' => 'border-accent-amber/30'],
                    ['icon' => '🗳️', 'color' => 'from-accent-cyan/20 to-accent-cyan/5', 'border' => 'border-accent-cyan/30'],
                    ['icon' => '🏆', 'color' => 'from-accent-green/20 to-accent-green/5', 'border' => 'border-accent-green/30'],
                ];
            @endphp

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                @foreach($steps as $i => $s)
                    <div class="group relative bg-bg-card/60 backdrop-blur-sm border border-border-default rounded-2xl p-6 md:p-7 hover:border-accent-gold/30 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-accent-gold/5 animate-fadeInUp"
                         style="animation-delay: {{ $i * 100 }}ms">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br {{ $s['color'] }} border {{ $s['border'] }} flex items-center justify-center text-xl">
                                {{ $s['icon'] }}
                            </div>
                            <div class="flex-1 min-w-0 space-y-2">
                                <h3 class="font-semibold text-text-primary text-sm md:text-base">
                                    {{ __("ui.home.how_it_works_step_" . ($i + 1) . "_title") }}
                                </h3>
                                <p class="text-text-muted text-xs md:text-sm leading-relaxed">
                                    {{ __("ui.home.how_it_works_step_" . ($i + 1) . "_desc") }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Divider --}}
    <div class="max-w-3xl mx-auto px-4">
        <div class="h-px bg-gradient-to-r from-transparent via-border-default to-transparent"></div>
    </div>

    {{-- ==================== FACTIONS ==================== --}}
    <section id="factions" class="relative py-20 md:py-28 px-4 md:px-6 scroll-mt-16">
        <div class="max-w-6xl mx-auto">
            {{-- Section header --}}
            <div class="text-center mb-14 md:mb-18 space-y-4">
                <h2 class="font-serif text-3xl md:text-4xl text-text-primary font-bold">
                    {{ __('ui.home.factions_title') }}
                </h2>
                <p class="text-text-muted text-sm md:text-base max-w-xl mx-auto">
                    {{ __('ui.home.factions_subtitle') }}
                </p>
                <div class="w-16 h-0.5 bg-accent-gold/50 mx-auto rounded-full"></div>
            </div>

            @php
                $factions = [
                    [
                        'key' => 'village',
                        'icon' => '🏘️',
                        'color' => 'text-accent-blue',
                        'border' => 'border-accent-blue/30',
                        'bg' => 'from-accent-blue/5',
                        'roles' => ['seer', 'witch', 'hunter', 'bodyguard', 'elder', 'fox', 'cupid', 'bear_tamer'],
                    ],
                    [
                        'key' => 'werewolves',
                        'icon' => '🐺',
                        'color' => 'text-accent-red',
                        'border' => 'border-accent-red/30',
                        'bg' => 'from-accent-red/5',
                        'roles' => ['werewolf', 'white_werewolf', 'big_bad_wolf', 'wolf_hound', 'accursed_wolf_father'],
                    ],
                    [
                        'key' => 'neutral',
                        'icon' => '🎭',
                        'color' => 'text-accent-purple',
                        'border' => 'border-accent-purple/30',
                        'bg' => 'from-accent-purple/5',
                        'roles' => ['pied_piper', 'angel', 'village_idiot', 'scapegoat'],
                    ],
                ];
            @endphp

            <div class="grid md:grid-cols-3 gap-6 md:gap-8">
                @foreach($factions as $f)
                    <div class="relative bg-bg-card/60 backdrop-blur-sm border border-border-default rounded-2xl p-6 md:p-8 hover:border-accent-gold/30 transition-all duration-300 hover:-translate-y-1 animate-fadeInUp"
                         style="animation-delay: {{ $loop->index * 150 }}ms">
                        <div class="absolute inset-0 bg-gradient-to-br {{ $f['bg'] }} via-transparent to-transparent rounded-2xl pointer-events-none"></div>
                        <div class="relative z-10 space-y-4">
                            {{-- Header --}}
                            <div class="flex items-center gap-3">
                                <span class="text-2xl md:text-3xl">{{ $f['icon'] }}</span>
                                <h3 class="font-serif text-xl md:text-2xl font-bold {{ $f['color'] }}">
                                    {{ __("ui.home.factions_{$f['key']}_title") }}
                                </h3>
                            </div>

                            {{-- Description --}}
                            <p class="text-text-secondary text-sm leading-relaxed">
                                {{ __("ui.home.factions_{$f['key']}_desc") }}
                            </p>

                            {{-- Roles --}}
                            <div class="pt-2 space-y-1.5">
                                <p class="text-[10px] uppercase tracking-wider text-text-muted font-semibold">{{ __('ui.lobby.assigned_roles') }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($f['roles'] as $roleKey)
                                        @php
                                            $roleName = __("roles.{$roleKey}.name");
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 bg-bg-surface/60 border border-border-muted rounded-full text-[11px] text-text-secondary">
                                            {{ $roleName }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Divider --}}
    <div class="max-w-3xl mx-auto px-4">
        <div class="h-px bg-gradient-to-r from-transparent via-border-default to-transparent"></div>
    </div>

    {{-- ==================== FEATURES ==================== --}}
    <section class="relative py-20 md:py-28 px-4 md:px-6">
        <div class="max-w-6xl mx-auto">
            {{-- Section header --}}
            <div class="text-center mb-14 md:mb-18 space-y-4">
                <h2 class="font-serif text-3xl md:text-4xl text-text-primary font-bold">
                    {{ __('ui.home.features_title') }}
                </h2>
                <p class="text-text-muted text-sm md:text-base max-w-xl mx-auto">
                    {{ __('ui.home.features_subtitle') }}
                </p>
                <div class="w-16 h-0.5 bg-accent-gold/50 mx-auto rounded-full"></div>
            </div>

            @php
                $features = [
                    ['key' => 'narrator', 'icon' => '🎙️'],
                    ['key' => 'decoy', 'icon' => '🧩'],
                    ['key' => 'roles', 'icon' => '🎭'],
                    ['key' => 'voting', 'icon' => '🤫'],
                    ['key' => 'lovers', 'icon' => '💔'],
                    ['key' => 'bilingual', 'icon' => '🌐'],
                ];
            @endphp

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                @foreach($features as $f)
                    <div class="group bg-bg-card/60 backdrop-blur-sm border border-border-default rounded-2xl p-6 md:p-7 hover:border-accent-gold/30 transition-all duration-300 hover:-translate-y-1 animate-fadeInUp"
                         style="animation-delay: {{ $loop->index * 80 }}ms">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-accent-gold/20 to-accent-gold/5 border border-accent-gold/30 flex items-center justify-center text-lg mb-4">
                            {{ $f['icon'] }}
                        </div>
                        <h3 class="font-semibold text-text-primary text-sm md:text-base mb-2">
                            {{ __("ui.home.feature_{$f['key']}_title") }}
                        </h3>
                        <p class="text-text-muted text-xs md:text-sm leading-relaxed">
                            {{ __("ui.home.feature_{$f['key']}_desc") }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== CALL TO ACTION ==================== --}}
    <section class="relative py-20 md:py-28 px-4 md:px-6">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-accent-gold/[0.02] to-transparent pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl mx-auto text-center space-y-8">
            <div class="space-y-4 animate-fadeInUp">
                <h2 class="font-serif text-3xl md:text-4xl text-text-primary font-bold">
                    {{ __('ui.home.cta_title') }}
                </h2>
                <p class="text-text-secondary text-sm md:text-base">
                    {{ __('ui.home.cta_subtitle') }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 md:gap-4 animate-fadeInUp animation-delay-200">
                <a href="{{ route('rooms.create') }}"
                   class="group relative w-full sm:w-auto inline-flex items-center justify-center gap-2 px-10 py-4 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 hover:scale-[1.03] active:scale-95 shadow-lg shadow-accent-gold/20 text-sm md:text-base">
                    <span>{{ __('ui.home.cta_create') }}</span>
                    <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="{{ route('rooms.join') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-10 py-4 border-2 border-accent-gold/40 text-accent-gold font-bold rounded-xl hover:bg-accent-gold hover:text-bg-primary transition-all duration-200 hover:scale-[1.03] active:scale-95 text-sm md:text-base">
                    <span>{{ __('ui.home.cta_join') }}</span>
                </a>
            </div>
        </div>
    </section>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="border-t border-border-default/50 py-8 md:py-10 px-4 md:px-6">
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">🐺</span>
                <span class="font-serif font-semibold text-text-primary text-sm">{{ __('ui.app.name') }}</span>
            </div>
            <p class="text-text-muted/60 text-xs text-center">
                {{ __('ui.home.footer_text') }}
            </p>
            <div class="flex items-center gap-3">
                <x-language-switcher />
            </div>
        </div>
    </footer>

</div>
@endsection
