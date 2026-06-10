<div class="min-h-screen p-3 md:p-8"
     wire:poll.3s>
    <div class="max-w-6xl mx-auto space-y-6 md:space-y-8">

        {{-- Header --}}
        <div class="text-center space-y-2 md:space-y-3">
            <div class="text-2xl md:text-4xl font-serif font-bold text-accent-gold">{{ config('app.name') }}</div>
            <div class="glass-panel border border-accent-gold/30 inline-block px-4 py-2 md:px-6 md:py-3">
                <p class="text-[10px] md:text-xs text-text-muted uppercase tracking-widest mb-1">{{ __('ui.lobby.room_code') }}</p>
                <p class="font-mono text-2xl md:text-3xl tracking-[0.3em] text-accent-gold font-bold">{{ $room->code }}</p>
            </div>
            <div class="flex gap-2 justify-center">
                <button x-data x-init="$el._orig = $el.textContent"
                        @click="navigator.clipboard.writeText('{{ url('/join?code=' . $room->code) }}'); $el.textContent = '✓ ' + '{{ __('ui.lobby.copied') }}'; setTimeout(() => $el.textContent = $el._orig, 2000);"
                        class="text-xs text-accent-gold hover:text-accent-gold-dark transition-colors bg-accent-gold/10 hover:bg-accent-gold/20 px-3 py-1.5 rounded-lg font-medium">
                    📋 {{ __('ui.lobby.copy_link') }}
                </button>
                <a href="{{ url('/join?code=' . $room->code) }}" target="_blank"
                   class="text-xs text-text-muted hover:text-accent-gold transition-colors bg-bg-surface hover:bg-bg-elevated px-3 py-1.5 rounded-lg font-medium">
                    🔗 {{ __('ui.lobby.open_link') }}
                </a>
            </div>
        </div>

        {{-- QR + Players Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
            {{-- Left: QR Code & Player Count --}}
            <div class="flex flex-col items-center justify-center space-y-4 md:space-y-6">
                <div class="glass-panel border border-border-default p-3 md:p-4">
                    <img src="{{ $qrSvg }}" alt="QR Code" class="w-40 h-40 md:w-56 md:h-56">
                </div>
                <p class="text-text-muted text-xs md:text-sm">{{ __('ui.lobby.scan_to_join') }}</p>
                <div class="text-center">
                    <span class="text-2xl md:text-3xl font-bold font-mono text-accent-gold">{{ $playerCount }}</span>
                    <span class="text-text-muted text-sm"> / 24 {{ __('ui.lobby.players') }}</span>
                </div>
            </div>

            {{-- Right: Connected players --}}
            <div>
                <div class="glass-panel border border-border-default p-4 md:p-5">
                    <h3 class="text-text-primary font-semibold mb-3 md:mb-4 flex items-center gap-2 text-sm md:text-base">
                        <span>👥</span>
                        <span>{{ __('ui.lobby.connected_players') }}</span>
                        <span class="text-xs text-text-muted bg-bg-surface px-2 py-0.5 rounded-full font-mono">{{ $playerCount }}</span>
                    </h3>
                    <livewire:shared.player-list :room="$room" :wire:key="'player-list-'.$room->id" />
                </div>
            </div>
        </div>

        {{-- Role Configuration --}}
        <div>
            <h2 class="text-lg md:text-xl font-serif font-bold text-text-primary mb-4 md:mb-6 text-center">{{ __('ui.lobby.role_config') }}</h2>

            @foreach($roles as $faction => $factionRoles)
                @php
                    $factionConfig = match($faction) {
                        'village' => ['color' => 'text-accent-blue', 'border' => 'border-accent-blue/30', 'bg' => 'bg-accent-blue/5'],
                        'werewolves' => ['color' => 'text-accent-red', 'border' => 'border-accent-red/30', 'bg' => 'bg-accent-red/5'],
                        'neutral' => ['color' => 'text-accent-gold', 'border' => 'border-accent-gold/30', 'bg' => 'bg-accent-gold/5'],
                        default => ['color' => 'text-text-secondary', 'border' => 'border-border-default', 'bg' => 'bg-bg-surface/50'],
                    };
                @endphp
                <div class="mb-6 md:mb-8">
                    <h3 class="text-xs md:text-sm uppercase tracking-wider {{ $factionConfig['color'] }} font-semibold mb-3 md:mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full {{ str_replace('text-', 'bg-', $factionConfig['color']) }}"></span>
                        {{ __("ui.factions.{$faction}") }}
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-3">
                        @foreach($factionRoles as $role)
                            @php
                                $count = $roleCounts[$role->key] ?? 0;
                                $roleKey = $role->key;
                            @endphp
                            <div class="glass-panel border {{ $factionConfig['border'] }} overflow-hidden group hover:glow-gold transition-all duration-200">
                                <div class="p-2 md:p-3">
                                    <div class="flex items-center gap-1.5 md:gap-2 mb-1.5 md:mb-2">
                                        <x-role-icon :roleKey="$roleKey" class="text-base md:text-lg" />
                                        <span class="text-text-primary text-xs md:text-sm font-medium truncate">{{ __("roles.{$roleKey}.name") }}</span>
                                    </div>
                                    <p class="text-text-muted text-[9px] md:text-[10px] leading-relaxed line-clamp-2 mb-2 md:mb-3">
                                        {{ __("roles.{$roleKey}.description") }}
                                    </p>
                                    <div class="flex items-center justify-between bg-bg-surface/50 rounded-lg p-1">
                                        <button wire:click="decrementRole('{{ $roleKey }}')"
                                                class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center rounded-md text-text-muted hover:bg-bg-elevated hover:text-text-primary transition-colors text-sm md:text-base {{ $count <= 0 ? 'opacity-30 cursor-not-allowed' : '' }}"
                                                {{ $count <= 0 ? 'disabled' : '' }}>
                                            −
                                        </button>
                                        <span class="text-accent-gold font-mono text-xs md:text-sm font-bold w-6 md:w-8 text-center tabular-nums">{{ $count }}</span>
                                        <button wire:click="incrementRole('{{ $roleKey }}')"
                                                class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center rounded-md text-text-muted hover:bg-bg-elevated hover:text-text-primary transition-colors text-sm md:text-base">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Summary --}}
            <div class="glass-panel border border-border-default p-3 md:p-4 mb-4 md:mb-6">
                <div class="flex items-center justify-between flex-wrap gap-3 md:gap-4">
                    <div class="flex items-center gap-2 md:gap-4">
                        <span class="text-text-muted text-xs md:text-sm">{{ __('ui.lobby.assigned') }}:</span>
                        <span class="text-accent-gold font-mono text-lg md:text-xl font-bold">{{ array_sum($roleCounts) }}</span>
                        <span class="text-text-muted">/</span>
                        <span class="text-text-primary font-mono text-lg md:text-xl font-bold">{{ $playerCount }}</span>
                        <span class="text-text-muted text-xs md:text-sm">{{ __('ui.lobby.players') }}</span>
                    </div>
                    @if(array_sum($roleCounts) === $playerCount)
                        <div class="flex items-center gap-1.5 text-accent-green text-xs">
                            <span>✓</span>
                            <span>{{ __('ui.lobby.all_assigned') }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 text-accent-gold text-xs">
                            <span>⚠</span>
                            <span>{{ __('ui.lobby.needs_match') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Validation Errors --}}
            @if(!empty($validationErrors))
                <div class="glass-panel border border-accent-red/50 p-3 md:p-4 mb-4 md:mb-6">
                    <div class="space-y-1">
                        @foreach($validationErrors as $error)
                            <p class="text-accent-red text-xs">⚠ {{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Start Game --}}
            <div class="text-center">
                <button wire:click="startGame"
                        @if(!$canStart) disabled @endif
                        class="px-8 md:px-12 py-3 md:py-4 font-bold text-base md:text-lg rounded-xl transition-all duration-200 shadow-lg
                               {{ $canStart
                                    ? 'bg-accent-green text-white hover:bg-accent-green/90 hover:scale-105 active:scale-95 cursor-pointer glow-green'
                                    : 'bg-bg-elevated text-text-muted cursor-not-allowed opacity-50' }}">
                    {{ __('ui.button.start_game') }}
                </button>
            </div>
        </div>
    </div>
</div>
