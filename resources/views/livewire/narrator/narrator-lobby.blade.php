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

        {{-- Mode Toggle --}}
        <div class="flex justify-center">
            <div class="glass-panel border border-border-default inline-flex rounded-lg p-0.5">
                <button wire:click="toggleMode"
                        class="px-4 py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ $mode === 'beginner' ? 'bg-accent-gold text-white shadow-sm' : 'text-text-muted hover:text-text-primary' }}">
                    🎲 {{ __('ui.lobby.mode_beginner') }}
                </button>
                <button wire:click="toggleMode"
                        class="px-4 py-1.5 text-xs font-medium rounded-md transition-all duration-200
                               {{ $mode === 'advanced' ? 'bg-accent-gold text-white shadow-sm' : 'text-text-muted hover:text-text-primary' }}">
                    ⚙️ {{ __('ui.lobby.mode_advanced') }}
                </button>
            </div>
        </div>

        {{-- STEP 1: Expected Player Count --}}
        <div class="glass-panel border border-border-default p-4 md:p-6">
            <h3 class="text-sm md:text-base font-semibold text-text-primary mb-3 md:mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-accent-gold text-white text-xs flex items-center justify-center font-bold">1</span>
                <span>{{ __('ui.lobby.expected_players') }}</span>
            </h3>
            <div class="flex flex-col items-center gap-3">
                <div class="flex items-center gap-4">
                    <button wire:click="decrementExpectedPlayers"
                            class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-lg bg-bg-surface hover:bg-bg-elevated text-text-primary text-lg md:text-xl font-bold transition-colors
                                   {{ $expectedPlayerCount <= 0 ? 'opacity-30 cursor-not-allowed' : '' }}"
                            {{ $expectedPlayerCount <= 0 ? 'disabled' : '' }}>−</button>
                    <span class="text-3xl md:text-4xl font-bold font-mono text-accent-gold w-16 text-center tabular-nums">{{ $expectedPlayerCount > 0 ? $expectedPlayerCount : '?' }}</span>
                    <button wire:click="incrementExpectedPlayers"
                            class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-lg bg-bg-surface hover:bg-bg-elevated text-text-primary text-lg md:text-xl font-bold transition-colors
                                   {{ $expectedPlayerCount >= 24 ? 'opacity-30 cursor-not-allowed' : '' }}"
                            {{ $expectedPlayerCount >= 24 ? 'disabled' : '' }}>+</button>
                </div>
                @if($expectedPlayerCount > 0)
                    <div class="flex items-center gap-2 text-xs text-text-muted">
                        <span>{{ $playerCount }} {{ __('ui.lobby.players') }} {{ __('ui.lobby.connected_players') }}</span>
                        @if($playerCount < $expectedPlayerCount)
                            <span class="text-accent-gold">— {{ __('lobby.validation.waiting_for_players', ['expected' => $expectedPlayerCount, 'actual' => $playerCount]) }}</span>
                        @elseif($playerCount >= $expectedPlayerCount)
                            <span class="text-accent-green">✓ {{ __('ui.lobby.all_assigned') }}</span>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-text-muted">Set your target player count to begin.</p>
                @endif
            </div>
        </div>

        @if($expectedPlayerCount >= 4)
            {{-- Quick Setup (Presets) --}}
            <div class="glass-panel border border-border-default p-4 md:p-6">
                <h3 class="text-sm md:text-base font-semibold text-text-primary mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-accent-gold text-white text-xs flex items-center justify-center font-bold">2</span>
                    <span>🎲 {{ __('ui.lobby.presets') }}</span>
                    @if($activePreset)
                        <span class="text-xs bg-accent-gold/10 text-accent-gold px-2 py-0.5 rounded-full font-normal">
                            {{ __('ui.lobby.preset_applied', ['count' => $activePreset]) }}
                        </span>
                    @elseif($totalAssigned > 0)
                        <span class="text-xs bg-bg-surface text-text-muted px-2 py-0.5 rounded-full font-normal">
                            ⚙️ {{ __('ui.lobby.custom_config') }}
                        </span>
                    @endif
                </h3>

                @if($mode === 'beginner' && !$activePreset && $totalAssigned === 0)
                    {{-- Beginner mode: show prominent preset buttons --}}
                    <p class="text-xs text-text-muted mb-4">Choose a balanced setup for your player count:</p>
                @endif

                @php $presets = \App\Game\Services\RoleConfigValidator::getPresets(); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 md:gap-3 mb-4">
                    @foreach(array_keys($presets) as $presetCount)
                        @php $isActive = $activePreset === $presetCount; @endphp
                        @if($expectedPlayerCount >= $presetCount || $presetCount === $expectedPlayerCount)
                            <button wire:click="selectPreset({{ $presetCount }})"
                                    class="text-center p-2 md:p-3 rounded-lg border transition-all duration-200
                                           {{ $isActive
                                                ? 'border-accent-gold bg-accent-gold/10 text-accent-gold'
                                                : 'border-border-default bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary' }}">
                                <span class="block text-lg md:text-xl font-bold font-mono">{{ $presetCount }}</span>
                                <span class="block text-[10px] text-text-muted mt-0.5">{{ __('ui.lobby.players') }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Preset details for active preset --}}
                @if($activePreset && isset($presets[$activePreset]))
                    @php $presetRoles = $presets[$activePreset]; @endphp
                    <div class="flex flex-wrap gap-1.5 md:gap-2">
                        @foreach($presetRoles as $roleKey => $count)
                            @if($count > 0)
                                <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-bg-surface rounded-full px-2 py-0.5 text-text-secondary">
                                    <x-role-icon :roleKey="$roleKey" class="text-xs" />
                                    {{ $count }}× {{ __("roles.{$roleKey}.name") }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Preset confirmation modal --}}
                @if($pendingPreset)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                        <div class="glass-panel border border-border-default p-5 md:p-6 max-w-sm w-full rounded-xl shadow-2xl">
                            <h4 class="text-text-primary font-semibold text-sm md:text-base mb-3 text-center">
                                {{ __('ui.lobby.apply_preset', ['count' => $pendingPreset]) }}
                            </h4>
                            <p class="text-text-muted text-xs text-center mb-4">
                                {{ __('ui.lobby.role_config') }} {{ __('ui.lobby.presets') }}
                            </p>
                            <div class="flex gap-3 justify-center">
                                <button wire:click="cancelPreset"
                                        class="px-5 py-2 text-xs font-medium rounded-lg border border-border-default text-text-secondary hover:bg-bg-elevated transition-colors">
                                    {{ __('ui.button.cancel') }}
                                </button>
                                <button wire:click="confirmPreset"
                                        class="px-5 py-2 text-xs font-medium rounded-lg bg-accent-gold text-white hover:bg-accent-gold/90 transition-colors">
                                    {{ __('ui.button.confirm') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Guide Panel --}}
            <div x-data="{ open: false }" class="glass-panel border border-border-default p-3 md:p-4">
                <button @click="open = !open" class="w-full flex items-center justify-between gap-2 text-left">
                    <h3 class="text-xs md:text-sm font-semibold text-text-primary flex items-center gap-2">
                        <span>📖</span>
                        <span>{{ __('ui.lobby.setup_guide') }}</span>
                    </h3>
                    <span class="text-text-muted transition-transform duration-200" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" x-collapse class="mt-3 pt-3 border-t border-border-default">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="bg-bg-surface rounded-lg p-3">
                            <span class="font-bold text-text-primary block mb-1">4 {{ __('ui.lobby.players') }}</span>
                            <span class="text-text-secondary">1 🐺, 1 👁️, 2 🏘️</span>
                        </div>
                        <div class="bg-bg-surface rounded-lg p-3">
                            <span class="font-bold text-text-primary block mb-1">8 {{ __('ui.lobby.players') }}</span>
                            <span class="text-text-secondary">2 🐺, 1 👁️, 1 🧙, 1 🏹, 3 🏘️</span>
                        </div>
                        <div class="bg-bg-surface rounded-lg p-3">
                            <span class="font-bold text-text-primary block mb-1">12 {{ __('ui.lobby.players') }}</span>
                            <span class="text-text-secondary">3 🐺, 1 👁️, 1 🧙, 1 🏹, 1 🛡️, 5 🏘️</span>
                        </div>
                        <div class="bg-bg-surface rounded-lg p-3">
                            <span class="font-bold text-text-primary block mb-1">16 {{ __('ui.lobby.players') }}</span>
                            <span class="text-text-secondary">4 🐺, 1 👁️, 1 🧙, 1 🏹, 1 🛡️, 1 🦊, 7 🏘️</span>
                        </div>
                    </div>
                    <div class="mt-3 bg-bg-surface/50 rounded-lg p-3 text-xs">
                        <span class="font-semibold text-text-primary block mb-1">🐺 {{ __('ui.lobby.players') }} vs {{ __('ui.factions.werewolves') }}</span>
                        <div class="space-y-1 text-text-secondary">
                            <p>4–6 {{ __('ui.lobby.players') }} → 1 {{ __('ui.factions.werewolves') }}</p>
                            <p>7–9 {{ __('ui.lobby.players') }} → 2 {{ __('ui.factions.werewolves') }}</p>
                            <p>10–12 {{ __('ui.lobby.players') }} → 3 {{ __('ui.factions.werewolves') }}</p>
                            <p>13–15 {{ __('ui.lobby.players') }} → 4 {{ __('ui.factions.werewolves') }}</p>
                            <p>16–18 {{ __('ui.lobby.players') }} → 5 {{ __('ui.factions.werewolves') }}</p>
                            <p>19–24 {{ __('ui.lobby.players') }} → 6 {{ __('ui.factions.werewolves') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role Configuration --}}
            <div>
                <h3 class="text-sm md:text-base font-semibold text-text-primary mb-3 md:mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-accent-gold text-white text-xs flex items-center justify-center font-bold">{{ $mode === 'beginner' && $activePreset ? '✓' : '3' }}</span>
                    <span>{{ __('ui.lobby.role_config') }}</span>
                    @if($mode === 'beginner' && $activePreset)
                        <span class="text-[10px] text-text-muted font-normal">({{ __('ui.lobby.mode_advanced') }} {{ __('ui.lobby.presets') }})</span>
                    @endif
                </h3>

                @php $roleConfigValidator = app(\App\Game\Services\RoleConfigValidator::class); @endphp
                @foreach($roles as $faction => $factionRoles)
                    @php
                        $factionConfig = match($faction) {
                            'village' => ['color' => 'text-accent-blue', 'border' => 'border-accent-blue/30', 'bg' => 'bg-accent-blue/5'],
                            'werewolves' => ['color' => 'text-accent-red', 'border' => 'border-accent-red/30', 'bg' => 'bg-accent-red/5'],
                            'neutral' => ['color' => 'text-accent-gold', 'border' => 'border-accent-gold/30', 'bg' => 'bg-accent-gold/5'],
                            default => ['color' => 'text-text-secondary', 'border' => 'border-border-default', 'bg' => 'bg-bg-surface/50'],
                        };
                    @endphp
                    <div class="mb-4 md:mb-6">
                        <h4 class="text-xs md:text-sm uppercase tracking-wider {{ $factionConfig['color'] }} font-semibold mb-3 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full {{ str_replace('text-', 'bg-', $factionConfig['color']) }}"></span>
                            {{ __("ui.factions.{$faction}") }}
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-3">
                        @foreach($factionRoles as $role)
                                @php
                                    $count = $roleCounts[$role->key] ?? 0;
                                    $roleKey = $role->key;
                                    $totalAssignedLocal = array_sum($roleCounts);
                                    $atIndividualMax = $count >= $roleConfigValidator->getMaxForRole($roleKey);
                                    $atTotalMax = $effectiveCount > 0 && $totalAssignedLocal >= $effectiveCount;
                                    $isAtMax = $atIndividualMax || $atTotalMax;
                                    $hasRoleError = isset($roleErrors[$roleKey]);
                                @endphp
                                <div class="glass-panel border {{ $factionConfig['border'] }} overflow-hidden group hover:glow-gold transition-all duration-200 {{ $hasRoleError ? 'border-accent-red/50' : '' }}">
                                    <div class="p-2 md:p-3">
                                        <div class="flex items-center gap-1.5 md:gap-2 mb-1.5">
                                            <x-role-icon :roleKey="$roleKey" class="text-base md:text-lg" />
                                            <span class="text-text-primary text-xs md:text-sm font-medium truncate">{{ __("roles.{$roleKey}.name") }}</span>
                                        </div>
                                        <p class="text-text-muted text-[9px] md:text-[10px] leading-relaxed line-clamp-2 mb-2">
                                            {{ __("roles.{$roleKey}.description") }}
                                        </p>

                                        @if($hasRoleError)
                                            <div class="mb-1.5">
                                                @foreach($roleErrors[$roleKey] as $roleError)
                                                    <p class="text-accent-red text-[9px] leading-tight">⚠ {{ $roleError }}</p>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($atIndividualMax && $count > 0)
                                            <div class="mb-1.5">
                                                <p class="text-accent-gold text-[9px] leading-tight">✓ {{ __('ui.lobby.max_reached') }}</p>
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between bg-bg-surface/50 rounded-lg p-1">
                                            <button wire:click="decrementRole('{{ $roleKey }}')"
                                                    class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center rounded-md text-text-muted hover:bg-bg-elevated hover:text-text-primary transition-colors text-sm md:text-base {{ $count <= 0 ? 'opacity-30 cursor-not-allowed' : '' }}"
                                                    {{ $count <= 0 ? 'disabled' : '' }}>−</button>
                                            <span class="text-accent-gold font-mono text-xs md:text-sm font-bold w-6 md:w-8 text-center tabular-nums">{{ $count }}</span>
                                            <button wire:click="incrementRole('{{ $roleKey }}')"
                                                    class="w-7 h-7 md:w-8 md:h-8 flex items-center justify-center rounded-md transition-colors text-sm md:text-base
                                                           {{ $isAtMax ? 'text-text-muted/30 cursor-not-allowed' : 'text-text-muted hover:bg-bg-elevated hover:text-text-primary' }}"
                                                    {{ $isAtMax ? 'disabled' : '' }}>+</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Summary --}}
        <div class="glass-panel border border-border-default p-3 md:p-4">
            <div class="flex items-center justify-between flex-wrap gap-3 md:gap-4">
                <div class="flex items-center gap-2 md:gap-4">
                    <span class="text-text-muted text-xs md:text-sm">{{ __('ui.lobby.assigned') }}:</span>
                    <span class="text-accent-gold font-mono text-lg md:text-xl font-bold">{{ $totalAssigned }}</span>
                    <span class="text-text-muted">/</span>
                    <span class="text-text-primary font-mono text-lg md:text-xl font-bold">{{ $effectiveCount }}</span>
                    <span class="text-text-muted text-xs md:text-sm">{{ __('ui.lobby.players') }}</span>
                    @if($remaining > 0 && $expectedPlayerCount >= 4)
                        <span class="text-text-muted text-xs">({{ $remaining }} {{ __('ui.lobby.remaining') }})</span>
                    @endif
                </div>

                {{-- Balance Indicator --}}
                @if($effectiveCount >= 4 && $balanceStatus)
                    <div class="flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-lg
                        @switch($balanceStatus)
                            @case('balanced') bg-accent-green/10 text-accent-green @break
                            @case('slightly_village_favored')
                            @case('slightly_werewolf_favored')
                            @case('werewolf_favored')
                            @case('village_favored') bg-accent-gold/10 text-accent-gold @break
                            @case('unbalanced') bg-accent-red/10 text-accent-red @break
                            @default bg-bg-surface text-text-muted
                        @endswitch">
                        {{ __("ui.lobby.balance.{$balanceStatus}") }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Warnings --}}
        @if(!empty($warnings))
            <div class="glass-panel border border-accent-gold/40 bg-accent-gold/5 p-3 md:p-4">
                <div class="space-y-1">
                    @foreach($warnings as $warning)
                        <p class="text-accent-gold text-xs">⚠ {{ $warning }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if(!empty($validationErrors))
            <div class="glass-panel border border-accent-red/50 p-3 md:p-4">
                <div class="space-y-1">
                    @foreach($validationErrors as $error)
                        <p class="text-accent-red text-xs">⚠ {{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Session Flash Error --}}
        @if(session('error'))
            <div class="glass-panel border border-accent-red/50 p-3 md:p-4">
                <p class="text-accent-red text-xs">⚠ {{ session('error') }}</p>
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
