<div wire:poll.3s="tick" class="min-h-screen" x-data="{
    showOverlay: false,
    phaseLabel: '',
    phaseSubtitle: '',
    phaseIcon: '',
    phaseClass: '',
    sidebarTab: 'status',
    showRoleModal: false,
    modalPlayer: null,
    contextMenuPlayer: null,
    contextMenuX: 0,
    contextMenuY: 0,
    showPauseOverlay: {{ $paused ? 'true' : 'false' }},
    timerExpiredNotif: false,
    openContextMenu(player, event) {
        this.contextMenuPlayer = player;
        const rect = event.target.getBoundingClientRect();
        const menuWidth = 224;
        let x = rect.left;
        let y = rect.bottom + 4;
        if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 8;
        if (y + 200 > window.innerHeight) y = rect.top - 200;
        this.contextMenuX = x;
        this.contextMenuY = y;
    },
}" @transition-phase.window="
    showOverlay = true;
    phaseLabel = $event.detail.label;
    phaseSubtitle = $event.detail.subtitle || '';
    phaseIcon = $event.detail.icon || '';
    phaseClass = $event.detail.class;
    setTimeout(() => { showOverlay = false; }, 2000);
" @game-paused.window="showPauseOverlay = $event.detail.paused"
   @timer-expired.window="timerExpiredNotif = true; setTimeout(() => { timerExpiredNotif = false; }, 8000);">

    {{-- Phase transition overlay --}}
    <div x-show="showOverlay"
         class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden"
         :class="phaseClass"
         x-transition:enter="transition-all duration-700"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-all duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        {{-- Animated gradient sweep --}}
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/[0.03] to-transparent -translate-y-full"
             x-show="showOverlay"
             x-transition:enter="transition-all duration-1000"
             x-transition:enter-start="-translate-y-full"
             x-transition:enter-end="translate-y-full"
             style="transition-delay: 200ms;">
        </div>
        <div class="text-center space-y-4 relative z-10">
            <div class="text-6xl md:text-7xl animate-floatSlow" x-text="phaseIcon" style="animation-duration: 1s;"></div>
            <h2 class="text-4xl md:text-6xl font-serif font-bold text-text-primary animate-fadeInScale" style="animation-delay: 150ms;" x-text="phaseLabel"></h2>
            <p x-show="phaseSubtitle" class="text-lg md:text-xl text-text-secondary animate-slideUpReveal" style="animation-delay: 350ms;" x-text="phaseSubtitle"></p>
        </div>
    </div>

    {{-- Pause overlay --}}
    <div x-show="showPauseOverlay" x-cloak
         class="fixed inset-0 z-40 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="text-center space-y-4 animate-fadeInScale">
            <div class="text-6xl animate-pulse">⏸️</div>
            <h2 class="text-3xl font-serif font-bold text-accent-gold">{{ __('ui.narrator.game_paused') }}</h2>
            <p class="text-text-muted">{{ __('ui.narrator.game_paused_hint') }}</p>
        </div>
    </div>

    {{-- Timer expired notification --}}
    <div x-show="timerExpiredNotif" x-cloak
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 glass-panel border-2 border-accent-gold/60 rounded-xl px-6 py-4 shadow-2xl max-w-md w-full text-center animate-slideInDown">
        <div class="text-3xl mb-2">⏰</div>
        <p class="text-text-primary font-bold text-lg">{{ __('ui.narrator.timer_expired_title') }}</p>
        <p class="text-text-muted text-sm mt-1">{{ __('ui.narrator.timer_expired_hint') }}</p>
        <div class="flex gap-2 mt-3 justify-center">
            <button wire:click="extendTimer(30)"
                    class="px-3 py-1.5 bg-accent-blue text-white text-xs font-semibold rounded-lg hover:bg-accent-blue/90 transition-colors">
                +30s
            </button>
            <button wire:click="extendTimer(60)"
                    class="px-3 py-1.5 bg-accent-gold text-bg-primary text-xs font-semibold rounded-lg hover:bg-accent-gold-dark transition-colors">
                +1min
            </button>
            <button wire:click="dismissTimer"
                    class="px-3 py-1.5 bg-bg-surface text-text-secondary text-xs font-semibold rounded-lg hover:bg-bg-elevated transition-colors">
                {{ __('ui.narrator.dismiss_timer') }}
            </button>
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
                        <div class="text-4xl mb-2" x-text="modalPlayer.role ? modalPlayer.role.emoji : '❓'"></div>
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
                                    <span class="font-medium" x-text="modalPlayer.role.name"></span>
                                </div>
                                <div class="flex justify-between text-sm py-2 border-b border-border-default">
                                    <span class="text-text-muted">{{ __('ui.game.status') }}</span>
                                    <span :class="modalPlayer.is_alive ? 'text-accent-green' : 'text-accent-red'">
                                        <span x-text="modalPlayer.is_alive ? '{{ __('ui.game.alive') }}' : '{{ __('ui.game.dead') }}'"></span>
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm py-2 border-b border-border-default">
                                    <span class="text-text-muted">{{ __('ui.narrator.ready_status') }}</span>
                                    <span x-text="modalPlayer.is_ready ? '{{ __('ui.game.ready') }}' : '{{ __('ui.game.waiting') }}'"
                                          :class="modalPlayer.is_ready ? 'text-accent-green' : 'text-accent-gold'"></span>
                                </div>
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

    {{-- Player context menu --}}
    <div x-show="contextMenuPlayer !== null" x-cloak
         class="fixed inset-0 z-50"
         @click.away="contextMenuPlayer = null; contextMenuX = 0; contextMenuY = 0">
        <div class="absolute w-56 glass-panel border border-border-default shadow-2xl overflow-hidden"
             :style="{ left: contextMenuX + 'px', top: contextMenuY + 'px' }"
             x-transition:enter="transition-all duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>
            <div class="px-4 py-3 border-b border-border-default bg-bg-elevated/50">
                <p class="text-text-primary text-sm font-semibold truncate" x-text="contextMenuPlayer?.nickname || ''"></p>
                <p class="text-text-muted text-[10px]" x-show="contextMenuPlayer?.role_key" x-text="contextMenuPlayer?.role_name || ''"></p>
            </div>
            <div class="py-1 space-y-0.5 px-1">
                <button @click="showRoleModal = true; modalPlayer = contextMenuPlayer; contextMenuPlayer = null"
                        class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-text-primary hover:bg-bg-elevated rounded-lg transition-colors text-start">
                    <span class="text-base">👤</span>
                    <span>{{ __('ui.narrator.view_role') }}</span>
                </button>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-3 md:p-6 space-y-4">

        {{-- TIMER EXPIRED BANNER --}}
        @if($timerExpired && !$paused)
            <div class="glass-panel border border-accent-gold/50 p-3 flex items-center justify-between gap-3 animate-slideInDown">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">⏰</span>
                    <span class="text-xs text-accent-gold font-semibold">{{ __('ui.narrator.timer_expired_title') }}</span>
                </div>
                <div class="flex gap-2">
                    <button wire:click="extendTimer(30)"
                            class="px-3 py-1.5 bg-accent-blue text-white text-xs font-semibold rounded-lg hover:bg-accent-blue/90 transition-colors">
                        +30s
                    </button>
                    <button wire:click="extendTimer(60)"
                            class="px-3 py-1.5 bg-accent-gold text-bg-primary text-xs font-semibold rounded-lg hover:bg-accent-gold-dark transition-colors">
                        +1min
                    </button>
                    <button wire:click="dismissTimer"
                            class="px-3 py-1.5 bg-bg-surface text-text-secondary text-xs font-semibold rounded-lg hover:bg-bg-elevated transition-colors">
                        {{ __('ui.narrator.dismiss_timer') }}
                    </button>
                </div>
            </div>
        @endif

        {{-- TIMER INFO BAR --}}
        @if($timerRemaining !== null && !$paused)
            <div class="glass-panel border border-border-default p-2 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm">⏱️</span>
                    <span class="text-xs text-text-muted">{{ __('ui.game.time_remaining') }}</span>
                    <span class="font-mono text-sm {{ $timerRemaining <= 30 ? 'text-accent-red font-bold animate-pulse' : 'text-accent-gold' }}">
                        {{ gmdate('i:s', $timerRemaining) }}
                    </span>
                </div>
                <button wire:click="dismissTimer"
                        class="text-xs text-text-muted hover:text-text-secondary transition-colors">
                    {{ __('ui.narrator.dismiss_timer') }}
                </button>
            </div>
        @endif

        {{-- ===== TOP CONTROLS BAR ===== --}}
        <div class="glass-panel border border-border-default p-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                {{-- Game info --}}
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold font-serif text-text-primary">
                        {{ match($phase) { 'night' => '🌙', 'day' => '☀️', 'voting' => '🗳️', 'finished' => '🏆', default => '⏳' } }}
                    </span>
                    <div>
                        <span class="text-text-primary font-bold text-sm">{{ __("ui.phase.{$phase}") }}</span>
                        @if(!in_array($phase, ['waiting', 'finished']))
                            <span class="text-text-muted text-xs ms-2">{{ __('ui.game.round_short', ['number' => $state->round]) }}</span>
                        @endif
                    </div>
                    <span class="text-xs text-text-muted hidden sm:inline">{{ $totalAlive }}/{{ $players->count() }} {{ __('ui.game.alive') }}</span>
                    <span class="text-xs text-text-muted">🆔 {{ $room->code }}</span>
                </div>

                {{-- Pause/Resume --}}
                <button wire:click="pauseGame"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200
                               {{ $paused ? 'bg-accent-green text-white hover:bg-accent-green/90' : 'bg-accent-gold text-bg-primary hover:bg-accent-gold-dark' }}">
                    {{ $paused ? '▶️ '.__('ui.narrator.resume') : '⏸️ '.__('ui.narrator.pause') }}
                </button>
            </div>
        </div>

        {{-- ===== PHASE CONTROL BUTTONS ===== --}}
        @if(!$paused && $phase !== 'finished')
            <div class="flex flex-wrap gap-2 justify-center">
                @if($phase === 'night')
                    @if($isSequential)
                        @if($activeNightRole)
                            <button wire:click="skipCurrentNightRole"
                                    class="px-4 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                           bg-accent-blue text-white hover:bg-accent-blue-dark hover:scale-105 active:scale-95">
                                ⏭️ {{ __('ui.narrator.next_role') }}
                            </button>
                        @else
                            <button wire:click="activateNextRole"
                                    class="px-4 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                           bg-accent-blue text-white hover:bg-accent-blue-dark hover:scale-105 active:scale-95">
                                👤 {{ __('ui.narrator.wake_first_role') }}
                            </button>
                        @endif
                    @endif
                    <button wire:click="endNight"
                            wire:confirm="{{ __('ui.narrator.confirm_end_night') }}"
                            class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                   bg-accent-gold text-bg-primary hover:bg-accent-gold-dark hover:scale-105 active:scale-95">
                        🌅 {{ __('ui.narrator.end_night') }}
                    </button>
                    @if(count($pendingRoles) > 0)
                        <button wire:click="forceEndNight"
                                wire:confirm="{{ __('ui.narrator.confirm_force_end') }}"
                                class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                       bg-accent-red-dark text-white hover:bg-accent-red hover:scale-105 active:scale-95">
                            ⚠️ {{ __('ui.narrator.force_end_night') }}
                        </button>
                    @endif

                @elseif($phase === 'day')
                    @php
                        $firstDayVoting = $room->settings['first_day_voting'] ?? true;
                        $canVoteToday = $firstDayVoting || $state->round > 1;
                    @endphp
                    @if($canVoteToday)
                        <button wire:click="startVoting"
                                wire:confirm="{{ __('ui.narrator.confirm_start_voting') }}"
                                class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                       bg-accent-red text-white hover:bg-accent-red-dark hover:scale-105 active:scale-95">
                            🗳️ {{ __('ui.narrator.start_voting') }}
                        </button>
                    @else
                        <div class="text-center px-4 py-3 rounded-lg bg-bg-elevated border border-border-default">
                            <p class="text-xs text-text-muted">{{ __('ui.narrator.first_day_voting_blocked') }}</p>
                        </div>
                    @endif

                @elseif($phase === 'voting')
                    @if($votingTransitionNeeded)
                        <div class="flex flex-col gap-2">
                            <p class="text-xs text-text-muted text-center">{{ __('ui.narrator.vote_resolved_choose_next') }}</p>
                            <button wire:click="goToNightAfterVote"
                                    class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                           bg-accent-blue text-white hover:bg-accent-blue-dark hover:scale-105 active:scale-95">
                                🌙 {{ __('ui.narrator.go_to_night') }}
                            </button>
                        </div>
                    @else
                        <button wire:click="endVoting"
                                wire:confirm="{{ __('ui.narrator.confirm_end_voting') }}"
                                class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                       bg-accent-red-dark text-white hover:bg-accent-red hover:scale-105 active:scale-95">
                            📊 {{ __('ui.narrator.end_voting') }}
                        </button>
                    @endif

                @elseif($phase === 'waiting')
                    <button wire:click="startNight"
                            wire:confirm="{{ __('ui.narrator.confirm_start_night') }}"
                            class="px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                   bg-accent-blue text-white hover:bg-accent-blue-dark hover:scale-105 active:scale-95">
                        🌙 {{ __('ui.narrator.start_night') }}
                    </button>
                @endif
            </div>
        @endif

        {{-- ===== MAIN LAYOUT ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-6">

            {{-- LEFT: Player grid + Night progress --}}
            <div class="lg:col-span-3 space-y-4">

                {{-- NIGHT PROGRESS (Parallel mode) --}}
                @if($phase === 'night' && !$isSequential)
                    <div class="glass-panel border border-border-default overflow-hidden">
                        <div class="p-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ __('ui.narrator.night_actions') }}</span>
                            </div>
                            <div class="space-y-2">
                                @forelse($nightProgress as $roleKey => $progress)
                                    @php
                                        $emoji = match($roleKey) {
                                            'werewolf' => '🐺', 'big_bad_wolf' => '🐺', 'accursed_wolf_father' => '🐺',
                                            'white_werewolf' => '🐺', 'bodyguard' => '🛡️', 'seer' => '🔮',
                                            'witch' => '🧪', 'pied_piper' => '🎵', 'fox' => '🦊',
                                            'cupid' => '💘', 'wolf_hound' => '🐕', default => '❓',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between text-xs p-2 rounded-lg
                                                {{ $progress['completed'] ? 'bg-accent-green/5' : 'bg-bg-surface/30' }}">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $emoji }}</span>
                                            <span class="font-medium text-text-primary">{{ __("roles.{$roleKey}.name") }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="{{ $progress['completed'] ? 'text-accent-green' : 'text-accent-gold' }}">
                                                {{ $progress['done'] }}/{{ $progress['total'] }}
                                            </span>
                                            @if($progress['completed'])
                                                <span class="text-accent-green">✅</span>
                                            @else
                                                <span class="text-accent-gold animate-pulse">⏳</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <p class="text-text-muted text-xs">{{ __('ui.narrator.no_actions_yet') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                {{-- SEQUENTIAL NIGHT MODE panel --}}
                @if($phase === 'night' && $isSequential)
                    <div class="glass-panel border border-border-default overflow-hidden">
                        <div class="p-3">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ __('ui.narrator.night_sequence') }}</span>
                                <span class="text-xs text-text-muted">{{ $nightRoleOrderIndex + 1 }}/{{ count($nightRoleOrder) }}</span>
                            </div>
                            <div class="space-y-1">
                                @foreach($nightRoleOrder as $i => $rk)
                                    @php
                                        $isActive = $i === $nightRoleOrderIndex;
                                        $isPast = $i < $nightRoleOrderIndex;
                                        $isFuture = $i > $nightRoleOrderIndex;
                                        $emoji = match($rk) {
                                            'werewolf' => '🐺', 'big_bad_wolf' => '🐺', 'accursed_wolf_father' => '🐺',
                                            'white_werewolf' => '🐺', 'bodyguard' => '🛡️', 'seer' => '🔮',
                                            'witch' => '🧪', 'pied_piper' => '🎵', 'fox' => '🦊',
                                            'cupid' => '💘', 'wolf_hound' => '🐕', default => '❓',
                                        };
                                    @endphp
                                    <div class="flex items-center gap-2 text-xs p-2 rounded-lg transition-all duration-300
                                                {{ $isActive ? 'bg-accent-blue/10 ring-1 ring-accent-blue/30 font-semibold' : ($isPast ? 'bg-accent-green/5 opacity-60' : 'bg-bg-surface/20') }}">
                                        <span class="w-5 text-center">{{ $isPast ? '✅' : ($isActive ? '👤' : ($i + 1)) }}</span>
                                        <span>{{ $emoji }}</span>
                                        <span class="{{ $isActive ? 'text-accent-blue' : ($isPast ? 'text-text-muted' : 'text-text-muted/60') }}">
                                            {{ __("roles.{$rk}.name") }}
                                        </span>
                                        @if($isActive)
                                            <span class="ms-auto text-accent-blue animate-pulse text-[10px]">● {{ __('ui.narrator.active') }}</span>
                                        @elseif($isPast)
                                            <span class="ms-auto text-accent-green text-[10px]">✓</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if($activeNightRole)
                                <p class="text-xs text-accent-blue mt-3 text-center">
                                    👤 {{ __('ui.narrator.active_role_hint', ['role' => __("roles.{$activeNightRole}.name")]) }}
                                </p>
                                <div class="flex gap-2 mt-2">
                                    <button wire:click="skipCurrentNightRole"
                                            class="flex-1 py-2 bg-accent-blue text-white text-xs font-semibold rounded-lg hover:bg-accent-blue-dark transition-colors">
                                        ⏭️ {{ __('ui.narrator.next_role') }}
                                    </button>
                                    <button wire:click="skipCurrentNightRole"
                                            class="flex-1 py-2 bg-bg-surface text-text-secondary text-xs rounded-lg hover:bg-bg-elevated transition-colors">
                                        ⏩ {{ __('ui.narrator.skip_role') }}
                                    </button>
                                </div>
                            @else
                                <button wire:click="activateNextRole"
                                        class="w-full mt-3 py-2 bg-accent-blue text-white text-xs font-semibold rounded-lg hover:bg-accent-blue-dark transition-colors">
                                    👤 {{ __('ui.narrator.wake_first_role') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Bear Tamer growl --}}
                @if($phase === 'day' && $bearTamerGrowl)
                    <div class="glass-panel border border-accent-gold/50 p-3 flex items-center gap-3 animate-slideInDown">
                        <span class="text-2xl">🐻</span>
                        <div>
                            <p class="text-accent-gold text-xs font-semibold">{{ __('ui.narrator.bear_tamer_growl') }}</p>
                            <p class="text-text-muted text-[10px]">{{ __('ui.narrator.bear_tamer_growl_hint') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Little Girl Caught --}}
                @if($phase === 'night' && $littleGirlAlive)
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

                {{-- Night mode toggle (before night starts or during night) --}}
                @if($phase === 'night' || $phase === 'waiting')
                    <div class="glass-panel border border-border-default p-3">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ __('ui.narrator.night_mode') }}</span>
                            <div class="flex gap-1">
                                <button wire:click="setNightMode('sequential')"
                                        class="px-3 py-1.5 text-xs rounded-lg font-semibold transition-all duration-200
                                               {{ $isSequential ? 'bg-accent-blue text-white' : 'bg-bg-surface text-text-secondary hover:bg-bg-elevated' }}">
                                    {{ __('ui.narrator.mode_sequential') }}
                                </button>
                                <button wire:click="setNightMode('parallel')"
                                        class="px-3 py-1.5 text-xs rounded-lg font-semibold transition-all duration-200
                                               {{ !$isSequential ? 'bg-accent-blue text-white' : 'bg-bg-surface text-text-secondary hover:bg-bg-elevated' }}">
                                    {{ __('ui.narrator.mode_parallel') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Players grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 md:gap-3">
                    @forelse($players as $p)
                        @php
                            $isLover = isset($loverMap[$p->id]);
                            $isEnchanted = in_array($p->id, $enchantedIds);
                            $hasPending = collect($pendingRoles)->firstWhere('player_id', $p->id);
                            $isReady = $readyStatuses[$p->id]['is_ready'] ?? false;
                            $skippedPlayers = $state->data['skipped_players'] ?? [];
                            $isSkipped = in_array($p->id, $skippedPlayers);
                            $factionColors = [
                                'village' => 'border-s-accent-blue',
                                'werewolves' => 'border-s-accent-red',
                                'white_werewolf' => 'border-s-accent-purple',
                                'pied_piper' => 'border-s-accent-green',
                                'angel' => 'border-s-accent-gold',
                            ];
                            $borderColor = $factionColors[$p->role?->faction ?? 'village'] ?? 'border-s-border-default';
                        @endphp
                        @php
                            $_emoji = '';
                            if ($p->role) {
                                $_icons = ['villager'=>'🏘️','seer'=>'👁️','witch'=>'🧙','hunter'=>'🏹','bodyguard'=>'🛡️','little_girl'=>'👧','cupid'=>'💘','elder'=>'👑','scapegoat'=>'🐐','village_idiot'=>'🤡','two_sisters'=>'👭','three_brothers'=>'👬','stuttering_judge'=>'⚖️','knight_with_rusty_sword'=>'⚔️','devoted_servant'=>'🤝','bear_tamer'=>'🐻','fox'=>'🦊','werewolf'=>'🐺','big_bad_wolf'=>'🐾','accursed_wolf_father'=>'🦇','white_werewolf'=>'🌕','wolf_hound'=>'🐕','pied_piper'=>'🎵','angel'=>'😇'];
                                $_emoji = $_icons[$p->role->key] ?? '❓';
                            }
                        @endphp
                        @php
                            $_contextPlayer = [
                                'id' => $p->id,
                                'nickname' => $p->nickname,
                                'is_alive' => $p->is_alive,
                                'is_ready' => $isReady,
                                'phase' => $phase,
                                'role_key' => $p->role?->key,
                                'role_name' => $p->role ? __("roles.{$p->role->key}.name") : null,
                                'role' => $p->role ? [
                                    'key' => $p->role->key,
                                    'name' => __("roles.{$p->role->key}.name"),
                                    'faction' => __("ui.factions.{$p->role->faction}"),
                                    'emoji' => $_emoji,
                                ] : null,
                            ];
                        @endphp
                        <div @click="openContextMenu({{ json_encode($_contextPlayer) }}, $event)"
                             class="relative bg-bg-card border border-border-default rounded-xl p-2.5 cursor-pointer transition-all duration-200 hover:border-accent-gold/40 hover:shadow-lg group
                                    {{ !$p->is_alive ? 'opacity-50 grayscale' : 'hover:glow-gold' }}
                                    {{ $hasPending ? 'ring-1 ring-accent-blue/50' : '' }}
                                    {{ $isReady && $p->is_alive ? 'ring-1 ring-accent-green/40' : '' }}
                                    border-s-[3px] {{ $borderColor }}">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary">
                                        {{ strtoupper(substr($p->nickname, 0, 2)) }}
                                    </div>
                                    @if(!$p->is_alive)
                                        <div class="absolute -top-0.5 -end-0.5 w-3.5 h-3.5 bg-accent-red rounded-full flex items-center justify-center text-[8px]">💀</div>
                                    @elseif($isLover)
                                        <div class="absolute -top-0.5 -end-0.5 w-3 h-3 bg-accent-pink rounded-full flex items-center justify-center text-[7px]">💕</div>
                                    @elseif($isEnchanted)
                                        <div class="absolute -top-0.5 -end-0.5 w-3 h-3 bg-accent-green rounded-full flex items-center justify-center text-[7px]">✦</div>
                                    @elseif($isReady)
                                        <div class="absolute -top-0.5 -end-0.5 w-3 h-3 bg-accent-green rounded-full flex items-center justify-center text-[7px]">✓</div>
                                    @endif
                                    @if($hasPending && $p->is_alive)
                                        <div class="absolute -bottom-0.5 -start-0.5 w-3 h-3 bg-accent-blue rounded-full flex items-center justify-center text-[7px] animate-pulse">◉</div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-text-primary text-xs font-medium truncate {{ !$p->is_alive ? 'line-through text-text-muted' : '' }}">
                                        {{ $p->nickname }}
                                    </p>
                                    @if($p->role)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <x-role-icon :roleKey="$p->role->key" class="text-[10px]" />
                                            <span class="text-text-muted text-[9px] truncate">{{ __("roles.{$p->role->key}.name") }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-shrink-0 flex flex-col items-center gap-0.5">
                                    @if($p->is_alive && !$p->is_narrator)
                                        @if($isReady)
                                            <span class="w-1.5 h-1.5 rounded-full bg-accent-green inline-block" title="{{ __('ui.game.ready') }}"></span>
                                        @elseif(!$hasPending)
                                            <span class="w-1.5 h-1.5 rounded-full bg-accent-gold animate-pulse inline-block" title="{{ __('ui.game.waiting') }}"></span>
                                        @endif
                                    @endif
                                    @if(!$p->is_alive)
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent-red inline-block"></span>
                                    @endif
                                    @if($isSkipped && $p->is_alive)
                                        <span class="text-[8px] text-accent-red-dark">⏭️</span>
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
                        <button @click="sidebarTab = 'voting'"
                                class="flex-1 px-3 py-3 text-xs font-medium transition-all duration-200 whitespace-nowrap"
                                :class="sidebarTab === 'voting' ? 'text-accent-red border-b-2 border-accent-red bg-accent-red/5' : 'text-text-muted hover:text-text-secondary'">
                            🗳️ {{ __('ui.narrator.tab_voting_tab') }}
                        </button>
                        <button @click="sidebarTab = 'log'"
                                class="flex-1 px-3 py-3 text-xs font-medium transition-all duration-200 whitespace-nowrap"
                                :class="sidebarTab === 'log' ? 'text-accent-gold border-b-2 border-accent-gold bg-accent-gold/5' : 'text-text-muted hover:text-text-secondary'">
                            📜 {{ __('ui.narrator.tab_log') }}
                        </button>
                    </div>

                    {{-- Tab Content --}}
                    <div class="p-3 max-h-[calc(100vh-280px)] overflow-y-auto scrollbar-thin">
                        {{-- TAB: Status --}}
                        <div x-show="sidebarTab === 'status'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <div class="space-y-4">
                                {{-- Game status summary --}}
                                <div>
                                    <h4 class="text-xs uppercase tracking-wider text-text-muted font-semibold mb-2">{{ __('ui.narrator.game_status') }}</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        @php
                                            $aliveCount = $players->where('is_alive', true)->count();
                                            $deadCount = $players->where('is_alive', false)->count();
                                            $villageCount = $players->filter(fn($p) => $p->is_alive && $p->role && $p->role->faction === 'village')->count();
                                            $wolfCount = $players->filter(fn($p) => $p->is_alive && $p->role && $p->role->faction === 'werewolves')->count();
                                        @endphp
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-blue/20">
                                            <p class="text-accent-blue text-lg font-bold font-mono">{{ $villageCount }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.factions.village') }}</p>
                                        </div>
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-red/20">
                                            <p class="text-accent-red text-lg font-bold font-mono">{{ $wolfCount }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.factions.werewolves') }}</p>
                                        </div>
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-green/20">
                                            <p class="text-accent-green text-lg font-bold font-mono">{{ $aliveCount }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.game.alive') }}</p>
                                        </div>
                                        <div class="bg-bg-surface/50 rounded-lg p-3 border border-accent-red/20">
                                            <p class="text-accent-red text-lg font-bold font-mono">{{ $deadCount }}</p>
                                            <p class="text-xs text-text-muted">{{ __('ui.game.dead') }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Ready progress --}}
                                @if(!in_array($phase, ['waiting', 'finished']))
                                    <div>
                                        <h4 class="text-xs uppercase tracking-wider text-text-muted font-semibold mb-2">{{ __('ui.narrator.player_progress') }}</h4>
                                        <div class="bg-bg-surface/50 rounded-lg p-3">
                                            <div class="flex items-center justify-between text-xs mb-1">
                                                <span class="text-text-muted">{{ __('ui.narrator.ready_count') }}</span>
                                                <span class="font-mono font-semibold {{ $readyCount >= $totalActivePlayers ? 'text-accent-green' : 'text-accent-gold' }}">
                                                    {{ $readyCount }}/{{ $totalActivePlayers }}
                                                </span>
                                            </div>
                                            <div class="h-2 bg-bg-surface rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-300
                                                            {{ $readyCount >= $totalActivePlayers ? 'bg-accent-green' : 'bg-accent-gold' }}"
                                                     style="width: {{ $totalActivePlayers > 0 ? ($readyCount / $totalActivePlayers) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Lovers --}}
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

                                {{-- Enchanted --}}
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

                                {{-- Timer controls --}}
                                @if(!$paused && !in_array($phase, ['waiting', 'finished']))
                                    <div>
                                        <h4 class="text-xs uppercase tracking-wider text-accent-gold font-semibold mb-2">⏱️ {{ __('ui.narrator.timer') }}</h4>
                                        <div class="space-y-2">
                                            <button wire:click="startTimer"
                                                    class="w-full py-2 bg-bg-surface text-text-secondary text-xs font-semibold rounded-lg hover:bg-bg-elevated transition-colors">
                                                {{ $timerRemaining !== null ? __('ui.narrator.restart_timer') : __('ui.narrator.start_timer') }}
                                            </button>
                                            @if($timerRemaining !== null)
                                                <div class="flex gap-1">
                                                    <button wire:click="extendTimer(30)" class="flex-1 py-1.5 bg-accent-blue/10 text-accent-blue text-xs rounded-lg hover:bg-accent-blue/20 transition-colors">+30s</button>
                                                    <button wire:click="extendTimer(60)" class="flex-1 py-1.5 bg-accent-gold/10 text-accent-gold text-xs rounded-lg hover:bg-accent-gold/20 transition-colors">+60s</button>
                                                    <button wire:click="dismissTimer" class="flex-1 py-1.5 bg-bg-surface text-text-muted text-xs rounded-lg hover:bg-bg-elevated transition-colors">{{ __('ui.narrator.dismiss_timer') }}</button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- End Game button --}}
                                @if($phase !== 'finished')
                                    <div class="pt-2">
                                        <button wire:click="endGame"
                                                wire:confirm="{{ __('ui.narrator.confirm_end_game') }}"
                                                class="w-full py-2 bg-accent-red-dark text-white text-xs font-semibold rounded-lg hover:bg-accent-red transition-colors">
                                            🏁 {{ __('ui.narrator.end_game_early') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB: Night — only shows night actions --}}
                        <div x-show="sidebarTab === 'night'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <div class="space-y-3">
                                @if($phase === 'night')
                                    @if(empty($nightActionFeed))
                                        <div class="text-center py-8">
                                            <p class="text-text-muted text-xs">{{ __('ui.narrator.no_actions_yet') }}</p>
                                        </div>
                                    @else
                                        <div class="space-y-2" x-ref="nightFeed" x-effect="$nextTick(() => { if ($refs.nightFeed) { const el = $refs.nightFeed.closest('.overflow-y-auto') || $refs.nightFeed.parentElement; if (el) el.scrollTop = el.scrollHeight; }})">
                                            <h4 class="text-xs uppercase tracking-wider text-accent-blue font-semibold">{{ __('ui.narrator.action_feed') }}</h4>
                                            @foreach($nightActionFeed as $action)
                                                <div class="text-xs bg-bg-surface/30 rounded-lg px-3 py-2 border border-border-default flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <x-role-icon :roleKey="$action['role_key']" class="text-sm" />
                                                        <span class="text-text-primary font-medium">{{ $action['player_nickname'] }}</span>
                                                    </div>
                                                    <div>
                                                        @if($action['target_nickname'])
                                                            <span class="text-text-muted">→</span>
                                                            <span class="text-accent-gold">{{ $action['target_nickname'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-12">
                                        <p class="text-text-muted text-xs">{{ __('ui.narrator.no_actions_yet') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB: Voting — narrator sees tally + voter names --}}
                        <div x-show="sidebarTab === 'voting'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <div class="space-y-3">
                                @if($phase === 'voting')
                                    <h4 class="text-xs uppercase tracking-wider text-accent-red font-semibold">{{ __('ui.narrator.voting_progress') }}</h4>
                                    <div class="bg-bg-surface/30 rounded-lg p-3">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-text-muted">{{ __('ui.vote.cast') }}</span>
                                            <span class="font-mono font-semibold text-accent-red">{{ $voteCount }}/{{ $totalActivePlayers }}</span>
                                        </div>
                                        <div class="h-2 bg-bg-surface rounded-full overflow-hidden">
                                            <div class="h-full bg-accent-red rounded-full transition-all duration-500"
                                                 style="width: {{ $totalActivePlayers > 0 ? ($voteCount / $totalActivePlayers) * 100 : 0 }}%">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2 max-h-80 overflow-y-auto scrollbar-thin">
                                        @forelse($voteTally as $targetId => $count)
                                            @php
                                                $targetPlayer = $players->firstWhere('id', $targetId);
                                                $targetNick = $targetPlayer?->nickname ?? __('ui.vote.player_unknown', ['id' => $targetId]);
                                                $voters = $voteVoters[$targetId] ?? [];
                                            @endphp
                                            <div class="bg-bg-surface/30 rounded-lg p-3 border border-border-default">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-text-primary font-semibold text-sm">{{ $targetNick }}</span>
                                                    <span class="text-accent-red font-mono text-xs font-bold">{{ $count }} {{ __('ui.vote.votes') }}</span>
                                                </div>
                                                @if(!empty($voters))
                                                    <div class="text-[10px] text-text-muted">
                                                        <span class="uppercase tracking-wider">{{ __('ui.narrator.voted_by') }}:</span>
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach($voters as $voterNick)
                                                                <span class="px-1.5 py-0.5 bg-bg-elevated/50 rounded text-text-secondary">{{ $voterNick }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="text-text-muted text-xs text-center py-6 italic">{{ __('ui.vote.no_votes_yet') }}</p>
                                        @endforelse
                                    </div>
                                @else
                                    <div class="text-center py-12">
                                        <p class="text-text-muted text-xs">{{ __('ui.narrator.not_voting_phase') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB: Log --}}
                        <div x-show="sidebarTab === 'log'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <x-game-timeline :entries="$gameLog" />
                        </div>
                    </div>
                </div>

                {{-- Night mode info (when not night) --}}
                @if($phase !== 'night')
                    <div class="glass-panel border border-border-default p-3">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider">{{ __('ui.narrator.next_night_mode') }}</span>
                            <div class="flex gap-1">
                                <button wire:click="setNightMode('sequential')"
                                        class="px-3 py-1.5 text-xs rounded-lg font-semibold transition-all duration-200
                                               {{ $isSequential ? 'bg-accent-blue text-white' : 'bg-bg-surface text-text-secondary hover:bg-bg-elevated' }}">
                                    {{ __('ui.narrator.mode_sequential') }}
                                </button>
                                <button wire:click="setNightMode('parallel')"
                                        class="px-3 py-1.5 text-xs rounded-lg font-semibold transition-all duration-200
                                               {{ !$isSequential ? 'bg-accent-blue text-white' : 'bg-bg-surface text-text-secondary hover:bg-bg-elevated' }}">
                                    {{ __('ui.narrator.mode_parallel') }}
                                </button>
                            </div>
                        </div>
                        <p class="text-[10px] text-text-muted mt-2">{{ __('ui.narrator.night_mode_hint') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== GAME OVER SCREEN ===== --}}
    @if($phase === 'finished')
        @php $winningFaction = $state->data['winning_faction'] ?? 'no_one'; @endphp
        <x-end-game-screen
            :winningFaction="$winningFaction"
            :players="$players"
            :showNewGame="true"
            onNewGame="newGame"
        />
    @endif
</div>
