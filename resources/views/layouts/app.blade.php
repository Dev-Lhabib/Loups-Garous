<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="dark"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-bg-primary text-text-primary antialiased min-h-screen font-sans">
    {{-- Atmospheric background layers --}}
    <div class="fog-layer"></div>
    <div class="vignette"></div>

    {{-- Livewire loading indicator --}}
    <div wire:loading class="fixed top-0 inset-x-0 z-50 h-0.5">
        <div class="h-full bg-accent-gold animate-shimmer" style="background: linear-gradient(90deg, var(--color-accent-gold) 25%, var(--color-accent-gold-dark) 50%, var(--color-accent-gold) 75%); background-size: 200% 100%;"></div>
    </div>

    <div class="min-h-screen flex flex-col relative z-10">
        <x-app-header />
        <main class="flex-1">
            {{ $slot ?? '' }}
            @yield('content', '')
        </main>
    </div>

    @livewireScripts
</body>
</html>
