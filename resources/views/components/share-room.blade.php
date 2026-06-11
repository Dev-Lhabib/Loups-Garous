@props([
    'room' => null,
    'qrSvg' => null,
    'url' => null,
])

@php
    $roomCode = $room?->code ?? '';
    $shareUrl = $url ?? url('/join/' . $roomCode);
    $fullUrl = url('/join/' . $roomCode);
@endphp

<div x-data="{
    copied: false,
    showQr: false,
    copyLink() {
        navigator.clipboard.writeText('{{ $shareUrl }}').then(() => {
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        });
    },
    shareLink() {
        if (navigator.share) {
            navigator.share({
                title: '{{ config('app.name') }}',
                text: '{{ __('ui.lobby.share_room_text', ['code' => $roomCode]) }}',
                url: '{{ $shareUrl }}',
            }).catch(() => {});
        } else {
            this.copyLink();
        }
    },
    toggleQr() { this.showQr = !this.showQr; },
}"
     class="glass-panel border border-border-default p-4 md:p-5">

    <div class="flex items-center justify-between gap-3 mb-3">
        <div>
            <p class="text-[10px] md:text-xs text-text-muted uppercase tracking-widest">{{ __('ui.lobby.room_code') }}</p>
            <p class="font-mono text-lg md:text-xl tracking-[0.2em] text-accent-gold font-bold">{{ $roomCode }}</p>
        </div>
    </div>

    <p class="text-[10px] md:text-xs text-text-muted mb-4 break-all">{{ $shareUrl }}</p>

    <div class="flex flex-wrap gap-2">
        <button @click="copyLink"
                class="relative flex-1 min-w-[100px] flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium rounded-lg border border-border-default
                       bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary transition-all duration-200">
            <template x-if="!copied">
                <span class="flex items-center gap-1.5">📋 {{ __('ui.lobby.copy_link') }}</span>
            </template>
            <template x-if="copied">
                <span class="flex items-center gap-1.5 text-accent-green">✓ {{ __('ui.lobby.copied') }}</span>
            </template>
        </button>

        <button @click="shareLink"
                class="relative flex-1 min-w-[100px] flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium rounded-lg border border-border-default
                       bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary transition-all duration-200">
            <span class="flex items-center gap-1.5">📤 {{ __('ui.lobby.share_link') }}</span>
        </button>

        @if($qrSvg)
            <button @click="toggleQr"
                    class="flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium rounded-lg border border-border-default
                           bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary transition-all duration-200"
                    :class="showQr ? 'bg-accent-gold/10 border-accent-gold/30 text-accent-gold' : ''">
                <span>📱</span>
                <span class="hidden sm:inline">{{ __('ui.lobby.show_qr') }}</span>
            </button>
        @endif
    </div>

    @if($qrSvg)
        <div x-show="showQr"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mt-4 pt-4 border-t border-border-default flex flex-col items-center"
             x-cloak>
            <div class="glass-panel border border-border-default p-2 inline-block">
                <img src="{{ $qrSvg }}" alt="QR Code" class="w-32 h-32 md:w-40 md:h-40">
            </div>
            <p class="text-[10px] text-text-muted mt-2 text-center">{{ __('ui.lobby.scan_to_join') }}</p>
        </div>
    @endif

</div>
