@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center px-6 py-12">
    <div class="w-full max-w-sm mx-auto text-center space-y-10 animate-fadeInUp">

        {{-- Locale Toggle --}}
        <div class="absolute top-4 right-4 md:top-6 md:right-6 flex items-center gap-2 bg-bg-card/80 backdrop-blur-sm rounded-lg p-1 border border-border-default">
            <a href="{{ route('locale.switch', 'en') }}"
               class="text-sm px-3 py-1.5 rounded-md transition-all duration-200 font-medium
                      {{ app()->getLocale() === 'en' ? 'bg-accent-gold text-bg-primary shadow-lg' : 'text-text-muted hover:text-text-primary' }}">
                EN
            </a>
            <a href="{{ route('locale.switch', 'fr') }}"
               class="text-sm px-3 py-1.5 rounded-md transition-all duration-200 font-medium
                      {{ app()->getLocale() === 'fr' ? 'bg-accent-gold text-bg-primary shadow-lg' : 'text-text-muted hover:text-text-primary' }}">
                FR
            </a>
        </div>

        {{-- Brand --}}
        <div class="space-y-4">
            <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-accent-gold/30 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center animate-heartbeat">
                <span class="text-4xl">🐺</span>
            </div>
            <h1 class="font-serif text-4xl md:text-5xl text-text-primary font-bold tracking-wide">{{ config('app.name') }}</h1>
            <p class="text-text-muted text-sm max-w-xs mx-auto leading-relaxed">{{ __('ui.home.subtitle') }}</p>
        </div>

        {{-- Actions --}}
        <div class="space-y-4">
            <a href="{{ route('rooms.create') }}"
               class="block w-full py-4 px-6 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 hover:scale-[1.02] active:scale-95 shadow-lg text-center">
                {{ __('ui.button.create_room') }}
            </a>
            <a href="{{ route('rooms.join') }}"
               class="block w-full py-4 px-6 border-2 border-accent-gold/40 text-accent-gold font-bold rounded-xl hover:bg-accent-gold hover:text-bg-primary transition-all duration-200 hover:scale-[1.02] active:scale-95 text-center">
                {{ __('ui.button.join_room') }}
            </a>
        </div>

        {{-- Footer --}}
        <p class="text-text-muted/50 text-xs">Les Loups-Garous de Thiercelieux</p>
    </div>
</div>
@endsection
