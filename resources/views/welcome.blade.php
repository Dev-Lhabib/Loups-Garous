@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center px-4 md:px-6 py-8 md:py-12">
    <div class="w-full max-w-sm mx-auto text-center space-y-8 md:space-y-10 animate-fadeInUp">

        {{-- Brand --}}
        <div class="space-y-3 md:space-y-4">
            <div class="w-16 h-16 md:w-20 md:h-20 mx-auto rounded-full bg-gradient-to-br from-accent-gold/30 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center animate-floatSlow">
                <span class="text-3xl md:text-4xl">🐺</span>
            </div>
            <h1 class="font-serif text-3xl md:text-5xl text-text-primary font-bold tracking-wide">{{ config('app.name') }}</h1>
            <p class="text-text-muted text-xs md:text-sm max-w-xs mx-auto leading-relaxed px-2">{{ __('ui.home.subtitle') }}</p>
        </div>

        {{-- Actions --}}
        <div class="space-y-3 md:space-y-4">
            <a href="{{ route('rooms.create') }}"
               class="block w-full py-3 md:py-4 px-5 md:px-6 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 hover:scale-[1.02] active:scale-95 shadow-lg text-center text-sm md:text-base">
                {{ __('ui.button.create_room') }}
            </a>
            <a href="{{ route('rooms.join') }}"
               class="block w-full py-3 md:py-4 px-5 md:px-6 border-2 border-accent-gold/40 text-accent-gold font-bold rounded-xl hover:bg-accent-gold hover:text-bg-primary transition-all duration-200 hover:scale-[1.02] active:scale-95 text-center text-sm md:text-base">
                {{ __('ui.button.join_room') }}
            </a>
        </div>

        {{-- Footer --}}
        <p class="text-text-muted/50 text-xs">Les Loups-Garous de Thiercelieux</p>
    </div>
</div>
@endsection
