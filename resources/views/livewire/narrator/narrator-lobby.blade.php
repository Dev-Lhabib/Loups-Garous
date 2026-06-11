<div class="min-h-screen p-4 md:p-8"
     wire:poll.3s>
    <div class="max-w-4xl mx-auto space-y-5 md:space-y-8">

        {{-- Header: Room Code + Sharing --}}
        <div class="glass-panel border border-accent-gold/20 p-4 md:p-5">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-accent-gold/20 to-accent-gold/5 border border-accent-gold/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-lg md:text-xl">🐺</span>
                    </div>
                    <div>
                        <p class="text-[10px] md:text-xs text-text-muted uppercase tracking-widest">{{ __('ui.lobby.room_code') }}</p>
                        <p class="font-mono text-lg md:text-2xl tracking-[0.25em] text-accent-gold font-bold">{{ $room->code }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button x-data x-init="() => { $el._orig = $el.innerHTML }"
                            @click="navigator.clipboard.writeText('{{ $joinUrl }}');
                                     $el.innerHTML = '✓ {{ __('ui.lobby.copied') }}';
                                     setTimeout(() => $el.innerHTML = $el._orig, 2000);"
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border border-border-default bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary transition-all duration-200 whitespace-nowrap">
                        📋 <span class="hidden sm:inline">{{ __('ui.lobby.copy_link') }}</span>
                    </button>
                    <button x-data @click="if (navigator.share) { navigator.share({ title: '{{ config('app.name') }}', text: '{{ __('ui.lobby.share_room_text', ['code' => $room->code]) }}', url: '{{ $joinUrl }}' }); } else { navigator.clipboard.writeText('{{ $joinUrl }}'); }"
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border border-border-default bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary transition-all duration-200 whitespace-nowrap">
                        📤 <span class="hidden sm:inline">{{ __('ui.lobby.share_link') }}</span>
                    </button>
                    <button wire:click="$set('showQr', {{ $showQr ? 'false' : 'true' }})"
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg border transition-all duration-200 whitespace-nowrap
                                   {{ $showQr ? 'bg-accent-gold/10 border-accent-gold/30 text-accent-gold' : 'border-border-default bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary' }}">
                        📱
                    </button>
                </div>
            </div>
            @if($showQr)
                <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     class="mt-4 pt-4 border-t border-border-default flex flex-col items-center gap-3">
                    <div class="glass-panel border border-border-default p-2 inline-block">
                        <img src="{{ $qrSvg }}" alt="QR Code" class="w-32 h-32 md:w-40 md:h-40">
                    </div>
                    <p class="text-[10px] md:text-xs text-text-muted">{{ __('ui.lobby.scan_to_join') }}</p>
                </div>
            @endif
        </div>

        {{-- Mode Toggle --}}
        <div class="flex justify-center">
            <div class="glass-panel border border-border-default inline-flex rounded-lg p-0.5">
                <button wire:click="toggleMode"
                        class="px-5 py-2 text-xs font-semibold rounded-md transition-all duration-200
                               {{ $mode === 'beginner' ? 'bg-accent-gold text-bg-primary shadow-sm' : 'text-text-muted hover:text-text-primary' }}">
                    🎲 {{ __('ui.lobby.mode_beginner') }}
                </button>
                <button wire:click="toggleMode"
                        class="px-5 py-2 text-xs font-semibold rounded-md transition-all duration-200
                               {{ $mode === 'advanced' ? 'bg-accent-gold text-bg-primary shadow-sm' : 'text-text-muted hover:text-text-primary' }}">
                    ⚙️ {{ __('ui.lobby.mode_advanced') }}
                </button>
            </div>
        </div>

        {{-- Step 1: Expected Players --}}
        <div class="glass-panel border border-border-default p-4 md:p-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-accent-gold/20 border border-accent-gold/30 text-accent-gold text-xs md:text-sm font-bold">1</span>
                <h3 class="text-sm md:text-base font-bold text-text-primary">{{ __('ui.lobby.expected_players') }}</h3>
            </div>
            <div class="flex flex-col items-center gap-4">
                <div class="flex items-center gap-5">
                    <button wire:click="decrementExpectedPlayers"
                            class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center rounded-xl bg-bg-surface hover:bg-bg-elevated text-text-primary text-xl md:text-2xl font-bold transition-all duration-200 border border-border-default hover:border-accent-gold/30 active:scale-95
                                   {{ $expectedPlayerCount <= 4 ? 'opacity-30 cursor-not-allowed' : '' }}"
                            {{ $expectedPlayerCount <= 4 ? 'disabled' : '' }}>−</button>
                    <div class="text-center">
                        <span class="text-4xl md:text-5xl font-bold font-mono text-accent-gold tabular-nums">{{ $expectedPlayerCount }}</span>
                        <p class="text-[10px] md:text-xs text-text-muted mt-0.5">{{ __('ui.lobby.players') }}</p>
                    </div>
                    <button wire:click="incrementExpectedPlayers"
                            class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center rounded-xl bg-bg-surface hover:bg-bg-elevated text-text-primary text-xl md:text-2xl font-bold transition-all duration-200 border border-border-default hover:border-accent-gold/30 active:scale-95
                                   {{ $expectedPlayerCount >= 24 ? 'opacity-30 cursor-not-allowed' : '' }}"
                            {{ $expectedPlayerCount >= 24 ? 'disabled' : '' }}>+</button>
                </div>
                <div class="text-[10px] md:text-xs text-text-muted">
                    {{ __('ui.lobby.minimum') }}: 4 {{ __('ui.lobby.players') }}
                </div>
            </div>
        </div>

        {{-- Step 2: Connected Players Status --}}
        <div class="glass-panel border border-border-default p-4 md:p-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-accent-gold/20 border border-accent-gold/30 text-accent-gold text-xs md:text-sm font-bold">2</span>
                <h3 class="text-sm md:text-base font-bold text-text-primary">{{ __('ui.lobby.connected_players') }}</h3>
            </div>
            <div class="flex flex-col items-center gap-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl md:text-4xl font-bold font-mono {{ $playerCount > 0 ? 'text-accent-green' : 'text-text-muted' }} tabular-nums">{{ $playerCount }}</span>
                    <span class="text-text-muted text-lg md:text-xl">/</span>
                    <span class="text-2xl md:text-3xl font-bold font-mono text-text-primary">{{ $expectedPlayerCount }}</span>
                    <span class="text-text-muted text-sm">{{ __('ui.join.joined') }}</span>
                </div>
                <div class="text-xs font-medium">
                    @if($playerCount === 0)
                        <span class="text-text-muted">{{ __('ui.lobby.waiting_for_players_join') }}</span>
                    @elseif($playerCount < $expectedPlayerCount)
                        <span class="text-accent-gold">{{ __('ui.lobby.waiting_for_x_more', ['count' => $expectedPlayerCount - $playerCount]) }}</span>
                    @else
                        <span class="text-accent-green">✓ {{ __('ui.lobby.all_connected') }}</span>
                    @endif
                </div>
                <div class="w-full mt-2">
                    <div class="w-full h-2 rounded-full bg-bg-surface overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 ease-out
                            {{ $playerCount >= $expectedPlayerCount ? 'bg-accent-green' : 'bg-accent-gold' }}"
                             style="width: {{ $expectedPlayerCount > 0 ? min(100, ($playerCount / $expectedPlayerCount) * 100) : 0 }}%">
                        </div>
                    </div>
                </div>
                @if($playerCount > 0)
                    <div class="w-full mt-1">
                        <livewire:shared.player-list :room="$room" :wire:key="'player-list-'.$room->id" />
                    </div>
                @else
                    <p class="text-text-muted text-xs">{{ __('ui.lobby.no_players_yet') }}</p>
                @endif
            </div>
        </div>

        {{-- Step 3: Recommended Setup (Beginner Mode) --}}
        @if($mode === 'beginner' && $expectedPlayerCount >= 4)
            <div class="glass-panel border border-border-default p-4 md:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-accent-gold/20 border border-accent-gold/30 text-accent-gold text-xs md:text-sm font-bold">3</span>
                    <h3 class="text-sm md:text-base font-bold text-text-primary">{{ __('ui.lobby.recommended_setup') }}</h3>
                </div>

                @php
                    $setup = $recommendedSetup;
                    $setupWerewolfCount = $setup['werewolf'] ?? 0;
                    $totalSetupRoles = array_sum($setup);
                    $isSetupApplied = $setupApplied;
                @endphp

                <div class="bg-gradient-to-br from-bg-elevated/50 to-bg-surface/30 rounded-xl border border-border-default p-4 md:p-5">
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-text-primary">👥 {{ $expectedPlayerCount }} {{ __('ui.lobby.players') }}</span>
                            <span class="w-px h-3 bg-border-default"></span>
                            @php
                                $balanceLabel = match($balanceStatus) {
                                    'balanced' => '🟢 ' . __('ui.lobby.balance.balanced'),
                                    'slightly_village_favored' => '🟡 ' . __('ui.lobby.balance.slightly_village_favored'),
                                    'slightly_werewolf_favored' => '🟡 ' . __('ui.lobby.balance.slightly_werewolf_favored'),
                                    'werewolf_favored' => '🟡 ' . __('ui.lobby.balance.werewolf_favored'),
                                    'village_favored' => '🟡 ' . __('ui.lobby.balance.village_favored'),
                                    default => '🔴 ' . __('ui.lobby.balance.unbalanced'),
                                };
                            @endphp
                            <span class="text-xs font-medium
                                @switch($balanceStatus)
                                    @case('balanced') text-accent-green @break
                                    @case('slightly_village_favored')
                                    @case('slightly_werewolf_favored')
                                    @case('werewolf_favored')
                                    @case('village_favored') text-accent-gold @break
                                    @default text-accent-red
                                @endswitch">
                                {{ $balanceLabel }}
                            </span>
                        </div>
                        @if($isSetupApplied)
                            <span class="text-[10px] bg-accent-green/10 text-accent-green px-2 py-0.5 rounded-full font-medium">✓ {{ __('ui.lobby.setup_applied') }}</span>
                        @endif
                    </div>

                    <div class="space-y-1.5 mb-4">
                        @foreach($setup as $roleKey => $count)
                            @if($count > 0)
                                <div class="flex items-center justify-between py-1.5 px-3 rounded-lg bg-bg-surface/50">
                                    <div class="flex items-center gap-2">
                                        <x-role-icon :roleKey="$roleKey" class="text-sm md:text-base" />
                                        <span class="text-xs md:text-sm text-text-primary font-medium">{{ __("roles.{$roleKey}.name") }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs md:text-sm font-mono font-bold text-accent-gold">×{{ $count }}</span>
                                        @if($roleKey === 'villager' && $isSetupApplied)
                                            <span class="text-[9px] text-text-muted bg-bg-surface px-1.5 py-0.5 rounded-full">{{ __('ui.lobby.auto_filled') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if(!$isSetupApplied)
                        <button wire:click="applyRecommendedSetup"
                                class="w-full py-2.5 md:py-3 px-4 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 active:scale-95 shadow-lg text-xs md:text-sm">
                            {{ __('ui.lobby.apply_setup') }}
                        </button>

                        <div x-data="{ showWhy: false }" class="mt-3">
                            <button @click="showWhy = !showWhy" class="flex items-center gap-1.5 text-[10px] md:text-xs text-text-muted hover:text-accent-gold transition-colors w-full justify-center">
                                <span>💡</span>
                                <span>{{ __('ui.lobby.why_this_setup') }}</span>
                                <span class="transition-transform duration-200" :class="showWhy ? 'rotate-180' : ''">▼</span>
                            </button>
                            <div x-show="showWhy" x-collapse class="mt-3 pt-3 border-t border-border-default">
                                <ul class="space-y-1.5">
                                    @foreach($setup as $roleKey => $count)
                                        @if($count > 0 && isset($explanations[$roleKey]))
                                            <li class="text-[10px] md:text-xs text-text-secondary flex items-start gap-2">
                                                <span class="text-text-muted mt-0.5">•</span>
                                                <span>{{ $explanations[$roleKey] }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Werewolf Guide --}}
        <div x-data="{ open: false }" class="glass-panel border border-border-default p-3 md:p-4">
            <button @click="open = !open" class="w-full flex items-center justify-between gap-2 text-start">
                <h4 class="text-xs md:text-sm font-semibold text-text-primary flex items-center gap-2">
                    <span>🐺</span>
                    <span>{{ __('ui.lobby.werewolf_guide') }}</span>
                </h4>
                <span class="text-text-muted transition-transform duration-200 text-xs" :class="open ? 'rotate-180' : ''">▼</span>
            </button>
            <div x-show="open" x-collapse class="mt-3 pt-3 border-t border-border-default">
                <div class="space-y-1 text-[10px] md:text-xs text-text-secondary">
                    <div class="flex justify-between py-1 px-2 rounded bg-bg-surface/50">
                        <span>{{ __('ui.lobby.players') }} 4–6</span>
                        <span class="font-mono text-accent-gold">→ 1 🐺</span>
                    </div>
                    <div class="flex justify-between py-1 px-2 rounded bg-bg-surface/50">
                        <span>{{ __('ui.lobby.players') }} 7–9</span>
                        <span class="font-mono text-accent-gold">→ 2 🐺</span>
                    </div>
                    <div class="flex justify-between py-1 px-2 rounded bg-bg-surface/50">
                        <span>{{ __('ui.lobby.players') }} 10–12</span>
                        <span class="font-mono text-accent-gold">→ 3 🐺</span>
                    </div>
                    <div class="flex justify-between py-1 px-2 rounded bg-bg-surface/50">
                        <span>{{ __('ui.lobby.players') }} 13–15</span>
                        <span class="font-mono text-accent-gold">→ 4 🐺</span>
                    </div>
                    <div class="flex justify-between py-1 px-2 rounded bg-bg-surface/50">
                        <span>{{ __('ui.lobby.players') }} 16–18</span>
                        <span class="font-mono text-accent-gold">→ 5 🐺</span>
                    </div>
                    <div class="flex justify-between py-1 px-2 rounded bg-bg-surface/50">
                        <span>{{ __('ui.lobby.players') }} 19–24</span>
                        <span class="font-mono text-accent-gold">→ 6 🐺</span>
                    </div>
                </div>
                <p class="text-[9px] text-text-muted mt-2">{{ __('ui.lobby.werewolf_guide_note') }}</p>
            </div>
        </div>

        {{-- Advanced Mode: Role Configuration --}}
        @if($mode === 'advanced')
            <div class="glass-panel border border-border-default p-4 md:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full bg-accent-gold/20 border border-accent-gold/30 text-accent-gold text-xs md:text-sm font-bold">3</span>
                    <h3 class="text-sm md:text-base font-bold text-text-primary">{{ __('ui.lobby.role_config') }}</h3>
                </div>

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
                    <div class="mb-4 md:mb-5">
                        <h4 class="text-xs uppercase tracking-wider {{ $factionConfig['color'] }} font-semibold mb-3 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', $factionConfig['color']) }}"></span>
                            {{ __("ui.factions.{$faction}") }}
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($factionRoles as $role)
                                @php
                                    $count = $roleCounts[$role->key] ?? 0;
                                    $roleKey = $role->key;
                                    $totalAssignedLocal = array_sum($roleCounts);
                                    $atIndividualMax = $count >= $roleConfigValidator->getMaxForRole($roleKey);
                                    $atTotalMax = $effectiveCount > 0 && $totalAssignedLocal >= $effectiveCount;
                                    $isAtMax = $atIndividualMax || $atTotalMax;
                                    $hasRoleError = isset($roleErrors[$roleKey]);
                                    $isVillager = $roleKey === 'villager';
                                @endphp
                                <div class="glass-panel border {{ $factionConfig['border'] }} overflow-hidden transition-all duration-200 {{ $hasRoleError ? 'border-accent-red/50' : '' }}">
                                    <div class="p-2 md:p-3">
                                        <div class="flex items-center gap-1.5 mb-1.5">
                                            <x-role-icon :roleKey="$roleKey" class="text-base md:text-lg" />
                                            <span class="text-text-primary text-xs font-medium truncate">{{ __("roles.{$roleKey}.name") }}</span>
                                        </div>
                                        <p class="text-text-muted text-[9px] leading-relaxed line-clamp-2 mb-2">
                                            {{ __("roles.{$roleKey}.description") }}
                                        </p>
                                        @if($isVillager && $villagerAutoFilled && $mode === 'advanced')
                                            <div class="mb-1.5">
                                                <span class="text-[9px] text-text-muted bg-bg-surface px-1.5 py-0.5 rounded-full">{{ __('ui.lobby.auto_filled') }}</span>
                                            </div>
                                        @endif
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
                                                    class="w-7 h-7 flex items-center justify-center rounded-md text-text-muted hover:bg-bg-elevated hover:text-text-primary transition-colors text-sm {{ $count <= 0 ? 'opacity-30 cursor-not-allowed' : '' }}"
                                                    {{ $count <= 0 ? 'disabled' : '' }}>−</button>
                                            <span class="text-accent-gold font-mono text-xs font-bold w-6 text-center tabular-nums">{{ $count }}</span>
                                            <button wire:click="incrementRole('{{ $roleKey }}')"
                                                    class="w-7 h-7 flex items-center justify-center rounded-md transition-colors text-sm
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

        {{-- Configuration Summary --}}
        <div class="glass-panel border border-border-default p-4 md:p-6">
            <div class="flex items-center gap-2 mb-4">
                <h3 class="text-sm md:text-base font-bold text-text-primary">{{ __('ui.lobby.config_summary') }}</h3>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs md:text-sm">
                    <span class="text-text-muted">{{ __('ui.lobby.expected_players') }}</span>
                    <span class="font-mono font-bold text-text-primary">{{ $expectedPlayerCount }}</span>
                </div>
                <div class="flex items-center justify-between text-xs md:text-sm">
                    <span class="text-text-muted">{{ __('ui.lobby.assigned_roles') }}</span>
                    <span class="font-mono font-bold {{ $totalAssigned === $effectiveCount && $effectiveCount > 0 ? 'text-accent-green' : 'text-accent-gold' }}">{{ $totalAssigned }} / {{ $effectiveCount }}</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-bg-surface overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 ease-out
                        {{ $totalAssigned === $effectiveCount && $effectiveCount > 0 ? 'bg-accent-green' : 'bg-accent-gold' }}"
                         style="width: {{ $effectiveCount > 0 ? min(100, ($totalAssigned / $effectiveCount) * 100) : 0 }}%">
                    </div>
                </div>
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        @if($totalAssigned < $effectiveCount && $effectiveCount > 0)
                            <span class="text-[10px] md:text-xs text-accent-gold">⚠ {{ __('ui.lobby.missing_roles', ['count' => $remaining]) }}</span>
                        @elseif($totalAssigned === $effectiveCount && $effectiveCount > 0)
                            <span class="text-[10px] md:text-xs text-accent-green">✓ {{ __('ui.lobby.ready_to_start') }}</span>
                        @endif
                    </div>
                    @if($effectiveCount >= 4)
                        <div class="flex items-center gap-1.5 text-[10px] md:text-xs font-medium px-2 py-1 rounded-lg
                            @switch($balanceStatus)
                                @case('balanced') bg-accent-green/10 text-accent-green @break
                                @case('slightly_village_favored')
                                @case('slightly_werewolf_favored')
                                @case('werewolf_favored')
                                @case('village_favored') bg-accent-gold/10 text-accent-gold @break
                                @case('unbalanced') bg-accent-red/10 text-accent-red @break
                                @default bg-bg-surface text-text-muted
                            @endswitch">
                            @switch($balanceStatus)
                                @case('balanced') 🟢 @break
                                @case('slightly_village_favored') 🟡 @break
                                @case('slightly_werewolf_favored') 🟡 @break
                                @case('werewolf_favored') 🟡 @break
                                @case('village_favored') 🟡 @break
                                @default 🔴
                            @endswitch
                            {{ __("ui.lobby.balance.{$balanceStatus}") }}
                        </div>
                    @endif
                </div>
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

        {{-- Room Sharing --}}
        <div class="glass-panel border border-border-default p-4 md:p-6">
            <div class="flex items-center gap-2 mb-4">
                <h3 class="text-sm md:text-base font-bold text-text-primary">{{ __('ui.lobby.share_room') }}</h3>
            </div>
            <div class="flex flex-col items-center gap-4">
                @if($showQr)
                    <div class="glass-panel border border-border-default p-2 inline-block">
                        <img src="{{ $qrSvg }}" alt="QR Code" class="w-32 h-32 md:w-36 md:h-36">
                    </div>
                    <p class="text-[10px] md:text-xs text-text-muted">{{ __('ui.lobby.scan_to_join') }}</p>
                @endif
                <div class="flex gap-2 w-full max-w-sm">
                    <button x-data x-init="() => { $el._orig = $el.innerHTML }"
                            @click="navigator.clipboard.writeText('{{ $joinUrl }}');
                                     $el.innerHTML = '✓ ' + '{{ __('ui.lobby.copied') }}';
                                     setTimeout(() => $el.innerHTML = $el._orig, 2000);"
                            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium rounded-lg border border-border-default bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary transition-all duration-200 whitespace-nowrap">
                        📋 {{ __('ui.lobby.copy_link') }}
                    </button>
                    <button x-data @click="if (navigator.share) { navigator.share({ title: '{{ config('app.name') }}', text: '{{ __('ui.lobby.share_room_text', ['code' => $room->code]) }}', url: '{{ $joinUrl }}' }); } else { navigator.clipboard.writeText('{{ $joinUrl }}'); }"
                            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2.5 text-xs font-medium rounded-lg border border-border-default bg-bg-surface hover:bg-bg-elevated text-text-secondary hover:text-text-primary transition-all duration-200 whitespace-nowrap">
                        📤 {{ __('ui.lobby.share_link') }}
                    </button>
                </div>
                <p class="text-[9px] md:text-[10px] text-text-muted break-all text-center max-w-sm">{{ $joinUrl }}</p>
            </div>
        </div>

        {{-- Start Game --}}
        <div class="text-center pt-2 pb-6 md:pb-8">
            <button wire:click="startGame"
                    @if(!$canStart) disabled @endif
                    class="px-10 md:px-16 py-3.5 md:py-4 font-bold text-sm md:text-lg rounded-xl transition-all duration-200 shadow-lg
                           {{ $canStart
                                ? 'bg-accent-green text-white hover:bg-accent-green/90 hover:scale-105 active:scale-95 cursor-pointer glow-green'
                                : 'bg-bg-elevated text-text-muted cursor-not-allowed opacity-50' }}">
                ▶ {{ __('ui.button.start_game') }}
            </button>
            @if(!$canStart && $effectiveCount >= 4)
                <p class="text-text-muted text-[10px] md:text-xs mt-2">{{ __('ui.lobby.fix_errors_to_start') }}</p>
            @elseif($effectiveCount < 4)
                <p class="text-text-muted text-[10px] md:text-xs mt-2">{{ __('ui.lobby.min_players_to_start', ['count' => 4]) }}</p>
            @endif
        </div>

    </div>
</div>
