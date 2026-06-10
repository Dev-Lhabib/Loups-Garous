@php
    $locales = [
        'en' => ['label' => 'English', 'flag' => '🇺🇸', 'short' => 'EN'],
        'fr' => ['label' => 'Français', 'flag' => '🇫🇷', 'short' => 'FR'],
        'ar' => ['label' => 'العربية', 'flag' => '🇲🇦', 'short' => 'AR'],
    ];
    $current = app()->getLocale();
@endphp

<div x-data="{
    open: false,
    isMobile: window.innerWidth < 768,
    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => {
                const el = this.$refs.dropdown;
                if (el) {
                    el.addEventListener('click', (e) => e.stopPropagation());
                }
            });
        }
    },
    close() { this.open = false; },
    switchLocale(code) {
        this.open = false;
        fetch('{{ route('locale.switch', '') }}/' + code + '?redirect=' + encodeURIComponent(window.location.pathname + window.location.search))
            .then(() => window.location.reload());
    }
}" x-on:keydown.escape="close" class="relative">

    {{-- Trigger Button --}}
    <button @click="toggle"
            class="flex items-center gap-1 md:gap-1.5 px-2 md:px-3 py-1.5 rounded-lg text-xs md:text-sm font-medium
                   text-text-muted hover:text-text-primary hover:bg-bg-surface transition-all duration-200 border border-transparent hover:border-border-default"
            aria-label="{{ __('ui.language.switch') }}">
        <span class="text-sm md:text-base">{{ $locales[$current]['flag'] ?? '🌍' }}</span>
        <span class="hidden md:inline">{{ $locales[$current]['short'] ?? strtoupper($current) }}</span>
        <svg class="w-3 h-3 md:w-3.5 md:h-3.5 text-text-muted transition-transform duration-200" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         @click.away="close"
         x-ref="dropdown"
         class="absolute top-full end-0 mt-1.5 w-44 glass-panel border border-border-default rounded-xl shadow-2xl overflow-hidden z-50"
         style="display: none;"
         x-cloak>

        <div class="py-1">
            @foreach($locales as $code => $info)
                <button @click="switchLocale('{{ $code }}')"
                        class="w-full flex items-center gap-3 px-3 py-2.5 text-xs md:text-sm transition-colors duration-150
                               {{ $current === $code
                                    ? 'bg-accent-gold/10 text-accent-gold font-medium'
                                    : 'text-text-secondary hover:bg-bg-surface hover:text-text-primary' }}">
                    <span class="text-base">{{ $info['flag'] }}</span>
                     <span class="flex-1 text-start">{{ $info['label'] }}</span>
                    @if($current === $code)
                        <svg class="w-4 h-4 text-accent-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </button>
                @if(!$loop->last)
                    <div class="mx-3 border-t border-border-default/50"></div>
                @endif
            @endforeach
        </div>
    </div>

</div>
