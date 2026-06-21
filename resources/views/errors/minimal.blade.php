<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ __('ui.app.name') }}</title>

    {{-- Theme initialization --}}
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light');
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-bg-primary text-text-primary antialiased min-h-screen font-sans">
    {{-- Atmospheric layers --}}
    <div class="fog-layer"></div>
    <div class="vignette"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4" role="main">
        <div class="w-full max-w-sm mx-auto text-center space-y-8 animate-fadeInUp">

            {{-- Icon --}}
            @yield('icon')

            {{-- Title --}}
            <h1 class="font-serif text-2xl md:text-3xl text-text-primary font-bold tracking-wide">
                @yield('title')
            </h1>

            {{-- Message --}}
            <p class="text-text-secondary text-sm md:text-base leading-relaxed max-w-xs mx-auto">
                @yield('message')
            </p>

            {{-- Action --}}
            @yield('action')

            {{-- Subtle divider --}}
            <div class="pt-4 border-t border-border-muted">
                <p class="text-text-muted text-xs">{{ __('ui.app.name') }}</p>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
