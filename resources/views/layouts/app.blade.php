<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Theme initialization — prevents flash of wrong theme --}}
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light');
        }
    </script>

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
            {{-- Flash message banner --}}
            @if(session('error'))
                <div class="fixed top-4 inset-x-0 z-50 flex justify-center px-4 pointer-events-none">
                    <div class="max-w-md w-full bg-accent-red/95 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-2xl border border-accent-red/50 flex items-center gap-3 pointer-events-auto animate-fadeInUp"
                         x-data="{ show: true }"
                         x-show="show"
                         x-init="setTimeout(() => show = false, 6000)">
                        <span class="flex-shrink-0 text-lg">⚠️</span>
                        <p class="flex-1">{{ session('error') }}</p>
                        <button @click="show = false" class="flex-shrink-0 text-white/70 hover:text-white transition-colors">&times;</button>
                    </div>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content', '')
        </main>
    </div>

    {{-- Alpine store for theme management --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                mode: localStorage.getItem('theme') || 'dark',
                get isDark() {
                    return this.mode === 'dark';
                },
                get isLight() {
                    return this.mode === 'light';
                },
                toggle() {
                    document.documentElement.classList.add('theme-transitioning');
                    this.mode = this.mode === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('theme', this.mode);
                    document.documentElement.classList.toggle('light', this.mode === 'light');
                    setTimeout(() => {
                        document.documentElement.classList.remove('theme-transitioning');
                    }, 350);
                }
            });
        });
    </script>

    @livewireScripts
</body>
</html>
