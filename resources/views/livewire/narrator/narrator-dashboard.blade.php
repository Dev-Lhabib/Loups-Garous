 <div wire:poll.3s="tick" class="min-h-screen" x-data="{
     showOverlay: false,
     phaseLabel: '',
     phaseSubtitle: '',
     phaseIcon: '',
     phaseClass: '',
     sidebarTab: 'status',
     showRoleModal: false,
     modalPlayer: null,
     autoResolveCountdown: null,
     autoResolveTimer: null,
 }" x-init="
     $wire.$on('auto-resolve-countdown', (data) => {
         autoResolveCountdown = data.seconds;
         if (autoResolveTimer) clearInterval(autoResolveTimer);
         autoResolveTimer = setInterval(() => {
             autoResolveCountdown--;
             if (autoResolveCountdown <= 0) {
                 clearInterval(autoResolveTimer);
                 autoResolveTimer = null;
             }
         }, 1000);
     });
 "
 @transition-phase.window="
     showOverlay = true;
     phaseLabel = $event.detail.label;
     phaseSubtitle = $event.detail.subtitle || '';
     phaseIcon = $event.detail.icon || '';
     phaseClass = $event.detail.class;
     setTimeout(() => { showOverlay = false; }, 2000);
 ">
    {{-- Phase transition overlay - cinematic --}}
    <div x-show="showOverlay"
         class="fixed inset-0 z-50 flex items-center justify-center"
         :class="phaseClass"
         x-transition:enter="transition-all duration-700"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-all duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        <div class="text-center space-y-4">
            <div class="text-6xl md:text-7xl animate-floatSlow" x-text="phaseIcon"></div>
            <h2 class="text-4xl md:text-6xl font-serif font-bold text-text-primary animate-fadeInScale animate-bannerPulse" x-text="phaseLabel"></h2>
            <p x-show="phaseSubtitle" class="text-lg md:text-xl text-text-secondary animate-slideUpReveal" style="animation-delay: 300ms;" x-text="phaseSubtitle"></p>
            <div class="flex justify-center gap-2 mt-4">
                <span class="w-2 h-2 rounded-full bg-accent-gold animate-pulse animation-delay-200"></span>
                <span class="w-2 h-2 rounded-full bg-accent-gold animate-pulse animation-delay-400"></span>
                <span class="w-2 h-2 rounded-full bg-accent-gold animate-pulse animation-delay-600"></span>
            </div>
        </div>
    </div>

    {{-- Role detail modal --}}
    <div x-show="showRoleModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         @click.away="showRoleModal = false"
         x-transition:enter="transition-all duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="w-full max-w-sm animate-fadeInScale" @click.stop>
            <template x-if="modalPlayer">
                <div class="glass-panel border-2 border-accent-gold/40 overflow-hidden">
                    <div class="p-5 text-center">
                        <div class="text-4xl mb-2">
                            <template x-if="modalPlayer.role">
                                <x-role-icon :roleKey="'placeholder'" class="text-4xl" />
                            </template>
                        </div>
                        <h3 class="text-lg font-bold text-text-primary" x-text="modalPlayer.nickname"></h3>
                    </div>
                    <div class="px-5 pb-5 space-y-3">
                        <template x-if="modalPlayer.role">
                            <div>
                                <div class="flex justify-between text-sm py-2 border-b border-border-default">
                                    <span class="text-text-muted">{{ __('ui.role.faction') }}</span>
                                    <span class="font-medium" x-text="modalPlayer.role.faction"></span>
                                </div>
                                <div class="flex justify-between text-sm py-2 border-b border-border-default">
                                    <span class="text-text-muted">{{ __('ui.role.your_role') }}</span>
                                    <span class="font-medium" x-text="modalPlayer.role?.name"></span>
                                </div>
                                <div class="flex justify-between text-sm py-2 border-b border-border-default">
                                    <span class="text-text-muted">{{ __('ui.game.status') }}</span>
                                    <span :class="modalPlayer.is_alive ? 'text-accent-green' : 'text-accent-red'">
                                        <span x-text="modalPlayer.is_alive ? '{{ __('ui.game.alive') }}' : '{{ __('ui.game.dead') }}'"></span>
                                    </span>
                                </div>
                                <div class="text-sm py-2 text-text-secondary" x-text="modalPlayer.role?.description"></div>
                            </div>
                        </template>
                    </div>
                    <div class="px-5 pb-5">
                        <button @click="showRoleModal = false"
                                class="w-full py-2 bg-bg-elevated text-text-secondary rounded-lg hover:bg-border-default transition-colors text-sm">
                            {{ __('ui.button.close') }}
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-3 md:p-6 space-y-4">

        {{-- AUTO-RESOLVE BANNER --}}
        @if($state->phase === 'night' && $autoResolveTimeLeft !== null)
            <div class="glass-panel border border-accent-green/50 p-3 flex items-center justify-between gap-3 animate-slideInDown">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-accent-green animate-pulse"></span>
                    <span class="text-xs text-accent-green font-semibold">{{ __('ui.night.all_actions_submitted') }}</span>
                    <span x-show="autoResolveCountdown !== null && autoResolveCountdown > 0" class="text-xs text-accent-green font-mono">
                        {{ __('ui.night.resolve_prefix') }} <span x-text="autoResolveCountdown"></span>s
                    </span>
                </div>
                <button wire:click="advancePhase('day')"
                        class="px-3 py-1.5 bg-accent-green text-white text-xs font-semibold rounded-lg hover:bg-accent-green/90 transition-colors">
                    {{ __('ui.button.resolve_now') }}
                </button>
            </div>
        @endif

        {{-- NIGHT TIMEOUT WARNING --}}
        @if($state->phase === 'night' && $nightRemaining !== null && $nightRemaining <= 30)
            <div class="glass-panel border border-accent-red/50 p-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-accent-red animate-ping"></span>
                    <span class="text-xs text-accent-red font-semibold">{{ __('ui.night.auto_resolve_imminent') }} ({{ $nightRemaining }}s)</span>
                </div>
                <button wire:click="forceResolve"
                        class="px-3 py-1.5 bg-accent-red text-white text-xs font-semibold rounded-lg hover:bg-accent-red/90 transition-colors">
                    {{ __('ui.button.force_resolve') }}
                </button>
            </div>
        @endif

        {{-- ===== PHASE HEADER ===== --}}
        <x-phase-header
            :phase="$state->phase"
            :round="$state->round"
            :aliveCount="$totalAlive"
            :totalCount="$players->count()"
            :roomCode="$room->code"
            narratorView="true"
        />

        {{-- ===== PHASE CONTROLS ===== --}}
        @if(count($availableTransitions) > 0 && $state->phase !== 'finished')
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach($availableTransitions as $target)
                    @php
                        $btnConfig = match($target) {
                            'night' => ['bg' => 'bg-accent-blue', 'hover' => 'hover:bg-accent-blue-dark', 'text' => 'text-white'],
                            'day' => ['bg' => 'bg-accent-gold', 'hover' => 'hover:bg-accent-gold-dark', 'text' => 'text-bg-primary'],
                            'voting' => ['bg' => 'bg-accent-red', 'hover' => 'hover:bg-accent-red-dark', 'text' => 'text-white'],
                            'finished' => ['bg' => 'bg-accent-red-dark', 'hover' => 'hover:bg-accent-red', 'text' => 'text-white'],
                            default => ['bg' => 'bg-bg-elevated', 'hover' => 'hover:bg-border-default', 'text' => 'text-text-secondary'],
                        };
                    @endphp
                    <button wire:click="advancePhase('{{ $target }}')"
                            wire:confirm="{{ __('ui.narrator.confirm_transition') }}"
                            class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                   {{ $btnConfig['bg'] }} {{ $btnConfig['hover'] }} {{ $btnConfig['text'] }}
                                   hover:scale-105 active:scale-95">
                        {{ __("ui.phase.go_to_{$target}") }}
                    </button>
                @endforeach

                {{-- FORCE RESOLVE (emergency) --}}
                @if($state->phase === 'night' && count($pendingRoles) > 0)
                    <button wire:click="forceResolve"
                            wire:confirm="{{ __('ui.night.force_resolve_confirm') }}"
                            class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                   bg-accent-red-dark hover:bg-accent-red text-white hover:scale-105 active:scale-95">
                        {{ __('ui.button.force_resolve') }}
                    </button>
                @endif
            </div>
        @endif

        {{-- ===== MAIN CONTENT: 2-COLUMN LAYOUT ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-6">

            {{-- LEFT: Player grid --}}
            <div class="lg:col-span-3 space-y-4">
                {{-- BEAR TAMER GROWL --}}
                @if($state->phase === 'day' && $bearTamerGrowl)
                    <div class="glass-panel border border-accent-gold/50 p-3 flex items-center gap-3 animate-slideInDown">
                        <span class="text-2xl">🐻</span>
                        <div>
                            <p class="text-accent-gold text-xs font-semibold">{{ __('ui.narrator.bear_tamer_growl') }}</p>
                            <p class="text-text-muted text-[10px]">{{ __('ui.narrator.bear_tamer_growl_hint') }}</p>
                        </div>
                    </div>
                @endif

                {{-- NIGHT PROGRESS SECTION --}}
                @if($state->phase === 'night')
                    @php
                        $totalNightRoles = count($nightOrder);
                        $completedCount = count($completedRoleKeys);
                        $pendingCount = count($pendingRoleKeys);
                        $progressPct = $totalNightRoles > 0 ? round(($completedCount / $totalNightRoles) * 100) : 0;
                        $timePct = $nightRemaining > 0 ? round(($nightRemaining / 120) * 100) : 0;
                        $timerColor = $nightRemaining <= 30 ? 'bg-accent-red' : ($nightRemaining <= 60 ? 'bg-accent-gold' : 'bg-accent-blue');
                    @endphp

                    {{-- Night progress bar --}}
                    <div class="glass-panel border border-border-default overflow-hidden">
                        <div class="p-3">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-text-muted">{{ __('ui.night.night_progress') }}</span>
                                <span class="font-mono {{ $timerColor == 'bg-accent-red' ? 'text-accent-red font-bold animate-pulse' : 'text-text-secondary' }}">
                                    {{ gmdate('i:s', $nightRemaining) }}
                                </span>
                            </div>
                            <div class="h-2 bg-bg-surface rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 ease-linear {{ $timerColor }} animate-progressPulse"
                                     style="width: {{ $timePct }}%">
                                </div>
                            </div>
                        </div>
                        {{-- Role progress chips --}}
                        <div class="px-3 pb-3">
                            <div class="flex items-center justify-between text-[10px] text-text-muted mb-2">
                                <span>{{ __('ui.night.roles_completed') }}: <span class="text-accent-green font-semibold">{{ $completedCount }}/{{ $totalNightRoles }}</span></span>
                                <span>{{ __('ui.night.roles_pending') }}: <span class="{{ $pendingCount > 0 ? 'text-accent-red' : 'text-accent-green' }} font-semibold">{{ $pendingCount }}</span></span>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($nightOrder as $rk)
                                    @php
                                        $isDone = in_array($rk, $completedRoleKeys);
                                        $isWaiting = in_array($rk, $pendingRoleKeys);
                                    @endphp
                                    <span class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded-full transition-all duration-300
                                        {{ $isDone ? 'bg-accent-green/10 text-accent-green' : ($isWaiting ? 'bg-accent-blue/10 text-accent-blue animate-pulse' : 'bg-bg-surface/50 text-text-muted/40') }}">
                                        @if($isDone)
                                            ✓
                                        @elseif($isWaiting)
                                            ●
                                        @else
                                            ○
                                        @endif
                                        {{ __("roles.{$rk}.name") }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Little Girl Caught button --}}
                @if($state->phase === 'night' && $littleGirlAlive)
                    <div class="glass-panel border border-accent-pink/30 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">🎀</span>
                                <span class="text-xs text-accent-pink font-semibold">{{ __('ui.narrator.little_girl_caught') }}</span>
                            </div>
                            <button wire:click="littleGirlCaught"
                                    wire:confirm="{{ __('ui.narrator.confirm_little_girl') }}"
                                    class="px-3 py-1.5 bg-accent-pink text-white text-xs font-semibold rounded-lg hover:bg-accent-pink/90 transition-colors">
                                {{ __('ui.narrator.catch_little_girl') }}
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Night step indicator --}}
                @if($state->phase === 'night')
                    <div class="glass-panel border border-border-default p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-text-muted uppercase tracking-wider">{{ __('ui.night.night_sequence') }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @foreach($nightOrder as $i => $rk)
                                @php
                                    $isCompleted = in_array($rk, $completedRoleKeys);
                                    $isPending = in_array($rk, $pendingRoleKeys);
                                    $stepNum = $i + 1;
                                @endphp
                                <span class="inline-flex items-center gap-1 text-[10px] px-2 py-1 rounded-full transition-all duration-300
                                    {{ $isCompleted ? 'bg-accent-green/10 text-accent-green' : ($isPending ? 'bg-accent-blue/10 text-accent-blue ring-1 ring-accent-blue/30' : 'bg-bg-surface/50 text-text-muted/40') }}">
                                    <span class="font-mono text-[9px] opacity-60">{{ $stepNum }}.</span>
                                    {{ __("roles.{$rk}.name") }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Player grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 md:gap-3">
                    @forelse($players as $p)
                        @php
                            $isLover = isset($loverMap[$p->id]);
                            $isEnchanted = in_array($p->id, $enchantedIds);
                            $isDisconnected = collect($disconnectedPlayers)->firstWhere('id', $p->id);
                            $hasPending = collect($pendingRoles)->firstWhere('player_id', $p->id);
                            $factionColors = [
                                'village' => 'border-s-accent-blue',
                                'werewolves' => 'border-s-accent-red',
                                'white_werewolf' => 'border-s-accent-purple',
                                'pied_piper' => 'border-s-accent-green',
                                'angel' => 'border-s-accent-gold',
                            ];
                            $borderColor = $factionColors[$p->role?->faction ?? 'village'] ?? 'border-s-border-default';
                        @endphp
                        <div @click="showRoleModal = true; modalPlayer = {{ json_encode([
                            'id' => $p->id,
                            'nickname' => $p->nickname,
                            'is_alive' => $p->is_alive,
                            'role' => $p->role ? [
                                'key' => $p->role->key,
                                'name' => __("roles.{$p->role->key}.name"),
                                'faction' => __("ui.factions.{$p->role->faction}"),
                                'description' => __("roles.{$p->role->key}.description"),
                            ] : null,
                        ]) }}"
                             class="relative bg-bg-card border border-border-default rounded-xl p-2.5 cursor-pointer transition-all duration-200 hover:border-accent-gold/40 hover:shadow-lg group
                                    {{ !$p->is_alive ? 'opacity-50 grayscale' : 'hover:glow-gold' }}
                                    {{ $hasPending ? 'ring-1 ring-accent-blue/50' : '' }}
                                    {{ $isDisconnected ? 'opacity-60 ring-1 ring-accent-red/50' : '' }}
                                    border-s-[3px] {{ $borderColor }}">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary">
                                        {{ strtoupper(substr($p->nickname, 0, 2)) }}
                                    </div>
                                    @if(!$p->is_alive)
                                        <div class="absolute -top-0.5 -end-0.5 w-3.5 h-3.5 bg-accent-red rounded-full flex items-center justify-center text-[8px]">💀</div>
                                    @elseif($isDisconnected)
                                        <div class="absolute -top-0.5 -end-0.5 w-3.5 h-3.5 bg-accent-red-dark rounded-full flex items-center justify-center text-[7px]">⚠</div>
                                    @elseif($isLover)
                                        <div class="absolute -top-0.5 -end-0.5 w-3 h-3 bg-accent-pink rounded-full flex items-center justify-center text-[7px]">💕</div>
                                    @elseif($isEnchanted)
                                        <div class="absolute -top-0.5 -end-0.5 w-3 h-3 bg-accent-green rounded-full flex items-center justify-center text-[7px]">✦</div>
                                    @endif
                                    {{-- Pending action indicator --}}
                                    @if($hasPending)
                                        <div class="absolute -bottom-0.5 -start-0.5 w-3 h-3 bg-accent-blue rounded-full flex items-center justify-center text-[7px] animate-pulse">◉</div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-text-primary text-xs font-medium truncate {{ !$p->is_alive ? 'line-through text-text-muted' : '' }}">
                                        {{ $p->nickname }}
                                        @if($isDisconnected)
                                            <span class="text-accent-red text-[9px] ms-0.5">({{ __('ui.game.disconnected') }})</span>
                                        @endif
                                    </p>
                                    @if($p->role)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <x-role-icon :roleKey="$p->role->key" class="text-[10px]" />
                                            <span class="text-text-muted text-[9px] truncate">{{ __("roles.{$p->role->key}.name") }}</span>
                                        </div>
                                    @endif
                                </div>
                                {{-- Status dot --}}
                                <div class="flex-shrink-0">
                                    @if(!$p->is_alive)
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent-red inline-block"></span>
                                    @elseif($hasPending)
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent-blue animate-pulse inline-block"></span>
                                    @elseif($isDisconnected)
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent-red-dark inline-block"></span>
                                    @elseif($isLover)
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent-pink inline-block"></span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent-green inline-block"></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-text-muted italic">{{ __('ui.narrator.no_players') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: Tabbed Sidebar --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="glass-panel border border-border-default overflow-hidden">
                    {{-- Tabs --}}
                    <div class="flex border-b border-border-default overflow-x-auto">
                        <button @click="sidebarTab = 'status'"
                                class="flex-1 px-3 py-3 text-xs font-medium transition-all duration-200 whitespace-nowrap"
                                :class="sidebarTab === 'status' ? 'text-accent-gold border-b-2 border-accent-gold bg-accent-gold/5' : 'text-text-muted hover:text-text-secondary'">
                            📊 {{ __('ui.narrator.tab_status') }}
                        </button>
                        <button @click="sidebarTab = 'night'"
                                class="flex-1 px-3 py-3 text-xs font-medium transition-all duration-200 whitespace-nowrap"
                                :class="sidebarTab === 'night' ? 'text-accent-blue border-b-2 border-accent-blue bg-accent-blue/5' : 'text-text-muted hover:text-text-secondary'">
                            🌙 {{ __('ui.narrator.tab_night') }}
                        </button>
                        <button @click="sidebarTab = 'log'"
                                class="flex-1 px-3 py-3 text-xs font-medium transition-all duration-200 whitespace-nowrap"
                                :class="sidebarTab === 'log' ? 'text-accent-gold border-b-2 border-accent-gold bg-accent-gold/5' : 'text-text-muted hover:text-text-secondary'">
                            📜 {{ __('ui.narrator.tab_log') }}
                        </button>
                        <button @click="sidebarTab = 'debug'"
                                class="flex-1 px-3 py-3 text-xs font-medium transition-all duration-200 whitespace-nowrap"
                                :class="sidebarTab === 'debug' ? 'text-accent-purple border-b-2 border-accent-purple bg-accent-purple/5' : 'text-text-muted hover:text-text-secondary'">
                            🔧 {{ __('ui.narrator.tab_debug') }}
                        </button>
                    </div>

                    {{-- Tab Content --}}
                    <div class="p-3 max-h-[calc(100vh-280px)] overflow-y-auto scrollbar-thin">
                        {{-- TAB: Status --}}
                        <div x-show="sidebarTab === 'status'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <div class="space-y-4">
                                {{-- Faction counts --}}
                                <div>
                                    <h4 class="text-xs uppercase tracking-wider text-text-muted font-semibold mb-2">{{ __('ui.narrator.game_status') }}</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        @php
                                            $alivePlayers = $players->where('is_alive', true);
                                            $deadPlayers = $players->where('is_alive', false);
                                            $factionCounts = [
                                                'village' => $alivePlayers->filter(fn($p) => $p->role && $p->role->faction === 'village')->count(),
                                                'werewolves' => $alivePlayers->filter(fn($p) => $p->role && $p->role->faction === 'werewolves')->count(),
                                                'neutral' => $alivePlayers->filter(fn($p) => $p->role && in_array($p->role->faction, ['white_werewolf', 'pied_piper', 'angel']))->count(),
                                            ];
                                        @endphp
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-blue/20">
                                            <p class="text-accent-blue text-lg font-bold font-mono">{{ $factionCounts['village'] }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.factions.village') }}</p>
                                        </div>
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-red/20">
                                            <p class="text-accent-red text-lg font-bold font-mono">{{ $factionCounts['werewolves'] }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.factions.werewolves') }}</p>
                                        </div>
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-gold/20">
                                            <p class="text-accent-gold text-lg font-bold font-mono">{{ $factionCounts['neutral'] }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.factions.neutral') }}</p>
                                        </div>
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-red/20">
                                            <p class="text-accent-red text-lg font-bold font-mono">{{ $deadPlayers->count() }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.game.dead') }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Lovers info --}}
                                @if(!empty($loverMap))
                                    <div>
                                        <h4 class="text-xs uppercase tracking-wider text-accent-pink font-semibold mb-2 flex items-center gap-1">💕 {{ __('ui.game.lovers') }}</h4>
                                        <div class="space-y-1">
                                            @foreach($loverMap as $pid => $partnerId)
                                                @if($pid < $partnerId)
                                                    @php
                                                        $p1 = $players->firstWhere('id', $pid);
                                                        $p2 = $players->firstWhere('id', $partnerId);
                                                    @endphp
                                                    @if($p1 && $p2)
                                                        <div class="text-xs text-text-secondary bg-accent-pink/5 rounded px-2 py-1 border border-accent-pink/20">
                                                            💕 {{ $p1->nickname }} ↔ {{ $p2->nickname }}
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Enchanted info --}}
                                @if(!empty($enchantedIds))
                                    <div>
                                        <h4 class="text-xs uppercase tracking-wider text-accent-green font-semibold mb-2 flex items-center gap-1">✦ {{ __('ui.game.enchanted') }}</h4>
                                        <div class="space-y-1">
                                            @foreach($enchantedIds as $eid)
                                                @php $ep = $players->firstWhere('id', $eid); @endphp
                                                @if($ep)
                                                    <div class="text-xs text-text-secondary bg-accent-green/5 rounded px-2 py-1 border border-accent-green/20">
                                                        ✦ {{ $ep->nickname }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Alive/Dead player lists --}}
                                <div>
                                    <h4 class="text-xs uppercase tracking-wider text-accent-green font-semibold mb-2 flex items-center gap-1">✓ {{ __('ui.game.alive') }} ({{ $alivePlayers->count() }})</h4>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($alivePlayers as $ap)
                                            <span class="text-[11px] bg-accent-green/10 text-accent-green px-2 py-0.5 rounded-full font-medium">{{ $ap->nickname }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @if($deadPlayers->isNotEmpty())
                                    <div>
                                        <h4 class="text-xs uppercase tracking-wider text-accent-red font-semibold mb-2 flex items-center gap-1">💀 {{ __('ui.game.dead') }} ({{ $deadPlayers->count() }})</h4>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($deadPlayers as $dp)
                                                <span class="text-[11px] bg-accent-red/10 text-accent-red/70 px-2 py-0.5 rounded-full line-through">{{ $dp->nickname }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Disconnected players --}}
                                @if(count($disconnectedPlayers) > 0)
                                    <div>
                                        <h4 class="text-xs uppercase tracking-wider text-accent-red-dark font-semibold mb-2">⚠ {{ __('ui.game.disconnected') }} ({{ count($disconnectedPlayers) }})</h4>
                                        <div class="space-y-1">
                                            @foreach($disconnectedPlayers as $dp)
                                                <div class="text-xs bg-accent-red/10 text-accent-red px-2 py-1 rounded border border-accent-red/20 flex justify-between">
                                                    <span>{{ $dp['nickname'] }}</span>
                                                    <span class="text-[10px] opacity-70">{{ __('ui.game.seconds_ago', ['seconds' => $dp['elapsed']]) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB: Night --}}
                        <div x-show="sidebarTab === 'night'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <div class="space-y-3">
                                @if($state->phase === 'night')
                                    @php
                                        $submittedRoles = collect($nightActionFeed)->pluck('role_key')->unique()->values()->toArray();
                                    @endphp
                                    <x-night-sequence
                                        :nightOrder="$nightOrder"
                                        :pendingRoles="$pendingRoleKeys"
                                        :submittedRoles="$submittedRoles"
                                    />
                                    <x-night-timeline :actions="$nightActionFeed" />
                                @elseif($state->phase === 'voting')
                                    <x-vote-tally
                                        :tally="$voteTally"
                                        :voteCount="$voteCount"
                                        :totalVoters="$players->where('is_alive', true)->where('voting_banned', false)->count()"
                                        :players="$players"
                                    />
                                @else
                                    <div class="text-center py-12">
                                        <p class="text-text-muted text-xs">{{ __('ui.narrator.no_actions_yet') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB: Game Log --}}
                        <div x-show="sidebarTab === 'log'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <x-game-timeline :entries="$gameLog" />
                        </div>

                        {{-- TAB: Debug --}}
                        <div x-show="sidebarTab === 'debug'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <div class="space-y-3">
                                <div class="bg-bg-surface/50 rounded-lg p-3 border border-border-default">
                                    <h4 class="text-xs uppercase tracking-wider text-accent-purple font-semibold mb-2">🔧 {{ __('ui.narrator.debug_info') }}</h4>
                                    <div class="space-y-1.5 text-xs font-mono">
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.phase.current_phase') }}</span>
                                            <span class="text-accent-gold font-semibold">{{ $state->phase }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.game.round') }}</span>
                                            <span class="text-text-primary">{{ $state->round }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.game.players_alive') }}</span>
                                            <span class="text-text-primary">{{ $totalAlive }} / {{ $players->count() }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.night.pending_roles') }}</span>
                                            <span class="{{ count($pendingRoleKeys) > 0 ? 'text-accent-red' : 'text-accent-green' }}">
                                                {{ count($pendingRoleKeys) > 0 ? implode(', ', $pendingRoleKeys) : __('ui.game.none') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.night.completed_roles') }}</span>
                                            <span class="text-accent-green">{{ count($completedRoleKeys) > 0 ? implode(', ', $completedRoleKeys) : __('ui.game.none') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.game.disconnected') }}</span>
                                            <span class="{{ count($disconnectedPlayers) > 0 ? 'text-accent-red' : 'text-accent-green' }}">
                                                {{ count($disconnectedPlayers) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.night.time_elapsed') }}</span>
                                            <span class="text-text-primary">{{ gmdate('i:s', $nightElapsed) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.night.time_remaining') }}</span>
                                            <span class="{{ $nightRemaining <= 30 ? 'text-accent-red font-bold' : 'text-text-primary' }}">
                                                {{ gmdate('i:s', $nightRemaining) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-text-muted">{{ __('ui.night.auto_resolve') }}</span>
                                            <span class="{{ $autoResolveTimeLeft !== null ? 'text-accent-green' : 'text-text-muted' }}">
                                                {{ $autoResolveTimeLeft !== null ? $autoResolveTimeLeft . 's' : __('ui.game.waiting') }}
                                            </span>
                                        </div>
                                        @if(count($disconnectedPlayers) > 0)
                                            <div class="pt-2 border-t border-border-default">
                                                <p class="text-accent-red text-[10px] font-semibold mb-1">{{ __('ui.game.disconnected') }}:</p>
                                                @foreach($disconnectedPlayers as $dp)
                                                    <div class="flex justify-between text-[10px]">
                                                        <span class="text-text-secondary">{{ $dp['nickname'] }}</span>
                                                        <span class="text-text-muted">{{ $dp['elapsed'] }}s</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== GAME OVER SCREEN ===== --}}
    @if($state->phase === 'finished')
        @php $winningFaction = $state->data['winning_faction'] ?? 'no_one'; @endphp
        <x-end-game-screen
            :winningFaction="$winningFaction"
            :players="$players"
            :showNewGame="true"
            onNewGame="newGame"
        />
    @endif
</div>
