<div class="min-h-screen flex flex-col p-4 md:p-8"
     wire:poll.2s
     x-data="{
         showOverlay: false,
         phaseLabel: '',
         phaseSubtitle: '',
         phaseIcon: '',
         phaseClass: '',
         resultNotif: null,
         resultTimer: null,
         nightEliminated: null,
         nightTimer: null,
         showResults: false,
         paused: {{ $paused ? 'true' : 'false' }},
     }"
     @transition-phase.window="
         showOverlay = true;
         phaseLabel = $event.detail.label;
         phaseSubtitle = $event.detail.subtitle || '';
         phaseIcon = $event.detail.icon || '';
         phaseClass = $event.detail.class;
         setTimeout(() => { showOverlay = false; }, 2000);
     "
     @show-result.window="
         resultNotif = $event.detail;
         if (resultTimer) clearTimeout(resultTimer);
         resultTimer = setTimeout(() => { resultNotif = null; }, 8000);
     "
     @show-night-resolved.window="
         nightEliminated = $event.detail;
         if (nightTimer) clearTimeout(nightTimer);
         nightTimer = setTimeout(() => { nightEliminated = null; }, 10000);
     "
     @game-paused.window="paused = $event.detail.paused"
>
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
    <div x-show="paused" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
        <div class="text-center space-y-4 animate-fadeInScale">
            <div class="text-6xl animate-pulse">⏸️</div>
            <h2 class="text-3xl font-serif font-bold text-accent-gold">{{ __('ui.narrator.game_paused') }}</h2>
            <p class="text-text-muted">{{ __('ui.narrator.game_paused_player_hint') }}</p>
        </div>
    </div>

    {{-- Night resolving overlay --}}
    <div x-show="showOverlay && phaseLabel === '{{ __("ui.phase.day") }}'"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
         style="animation-delay: 800ms;"
         x-transition:enter="transition-all duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-cloak>
        <div class="text-center space-y-3">
            <div class="text-4xl animate-spin-slow">🌙</div>
            <p class="text-text-secondary text-lg font-medium">{{ __('ui.game.resolving_night') }}</p>
        </div>
    </div>

    {{-- Result notification --}}
    <div x-show="resultNotif" x-cloak
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 glass-panel border-2 border-accent-gold/60 rounded-xl px-6 py-4 shadow-2xl max-w-md w-full text-center animate-slideInDown"
         x-transition:leave="transition-all duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Seer result --}}
        <template x-if="resultNotif?.type === 'seer'">
            <div>
                <div class="text-2xl mb-1">👁️</div>
                <p class="text-text-muted text-xs uppercase tracking-widest" x-text="resultNotif.nickname"></p>
                <p class="text-text-primary text-lg mt-1">
                    {{ __('ui.result.faction_label') }}: <span class="text-accent-gold font-semibold" x-text="resultNotif.faction"></span>
                </p>
            </div>
        </template>

        {{-- Fox result --}}
        <template x-if="resultNotif?.type === 'fox'">
            <div>
                <div class="text-2xl mb-1">🦊</div>
                <p class="text-text-primary text-lg"
                   x-text="resultNotif.found ? '{{ __("ui.result.wolves_found") }}' : '{{ __("ui.result.no_wolves_found") }}'">
                </p>
            </div>
        </template>

        {{-- Lover died --}}
        <template x-if="resultNotif?.type === 'lover_died'">
            <div>
                <p class="text-text-muted text-lg font-bold">{{ __('ui.result.lover_died_title') }}</p>
                <p class="text-text-primary mt-1">
                    <span class="text-accent-gold font-semibold" x-text="resultNotif.partner_nickname"></span>
                    {{ __('ui.result.lover_died_text') }}
                </p>
            </div>
        </template>

        {{-- Village Idiot revealed --}}
        <template x-if="resultNotif?.type === 'village_idiot'">
            <div>
                <p class="text-text-muted text-lg font-bold">{{ __('ui.result.idiot_revealed') }}</p>
                <p class="text-text-primary mt-1" x-text="resultNotif.nickname"></p>
            </div>
        </template>
    </div>

    {{-- Night resolved notification --}}
    <div x-show="nightEliminated" x-cloak
         class="fixed top-24 left-1/2 -translate-x-1/2 z-40 glass-panel border-2 border-accent-gold/60 rounded-xl px-6 py-4 shadow-2xl max-w-md w-full text-center animate-slideInDown"
         x-transition:leave="transition-all duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="text-3xl mb-2">🌅</div>
        <p class="text-text-primary text-lg font-bold">{{ __('ui.result.night_eliminations') }}</p>
        <div class="mt-2 space-y-1">
            <template x-for="name in (nightEliminated?.eliminated || [])" :key="name">
                <p class="text-text-primary font-semibold text-lg animate-fadeInUp" x-text="name"></p>
            </template>
        </div>
        <p x-show="nightEliminated && nightEliminated.eliminated.length === 0" class="text-text-muted italic mt-2">
            {{ __('ui.result.no_deaths') }}
        </p>
    </div>

    {{-- Timer display --}}
    @if($timerRemaining !== null && !$paused)
        @php
            $timerSeconds = match($state?->phase) {
                'day' => $state->data['day_timer_config'] ?? 180,
                'voting' => $state->data['voting_timer_config'] ?? 60,
                'night' => $state->data['night_timer_config'] ?? 120,
                default => 180,
            };
            $timerPct = $timerSeconds > 0 ? ($timerRemaining / $timerSeconds) * 100 : 0;
        @endphp
        <div class="fixed top-4 right-4 z-30 glass-panel border border-border-default rounded-lg px-3 py-2 flex items-center gap-2">
            <span class="text-xs">⏱️</span>
            <span class="font-mono text-sm {{ $timerRemaining <= 30 ? 'text-accent-red font-bold animate-pulse' : 'text-accent-gold' }}">
                {{ gmdate('i:s', $timerRemaining) }}
            </span>
        </div>
        @if(in_array($state?->phase, ['day', 'voting']))
            <div class="w-full max-w-md mx-auto mb-2">
                <div class="h-1 bg-bg-surface rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000 ease-linear
                                {{ $timerRemaining <= 30 ? 'bg-accent-red' : 'bg-accent-gold' }}"
                         style="width: {{ max(0, $timerPct) }}%">
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="flex-1 flex flex-col items-center justify-center space-y-6 w-full max-w-2xl mx-auto">

        @if(!$state)
            <div class="text-center py-12 space-y-4">
                <div class="text-4xl animate-floatSlow">⏳</div>
                <p class="text-text-muted">{{ __('ui.game.waiting') }}</p>
            </div>
            @return
        @endif

        {{-- Phase Header --}}
        <x-phase-header
            :phase="$state->phase"
            :round="$state->round"
            :aliveCount="$state->phase !== 'finished' && $state->phase !== 'waiting' ? $playersAliveCount : 0"
            :totalCount="$state->phase !== 'finished' && $state->phase !== 'waiting' ? $playersTotalCount : 0"
            :roomCode="$room->code"
            playerView="true"
        />

        @if($state->phase === 'waiting')
            {{-- Ready phase --}}
            <livewire:player.secret-role-card :player="$player" :wire:key="'role-'.$player->id" />

            <div class="w-full max-w-md animate-fadeInUp">
                @if($ready)
                    <div class="glass-panel border border-accent-green/50 p-6 text-center">
                        <div class="text-4xl mb-3 text-accent-green animate-heartbeat">✓</div>
                        <p class="text-text-muted">{{ __('ui.game.waiting_for_others') }}</p>
                        <div class="flex justify-center gap-1.5 mt-4">
                            <span class="w-2.5 h-2.5 rounded-full bg-accent-green animate-pulse animation-delay-200"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-accent-green animate-pulse animation-delay-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-accent-green animate-pulse animation-delay-600"></span>
                        </div>
                    </div>
                @else
                    <div class="glass-panel border border-border-default p-6 text-center space-y-4">
                        <p class="text-text-muted text-sm">{{ __('ui.game.your_role') }}</p>
                        <button wire:click="readyUp"
                                class="w-full py-4 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 text-lg hover:scale-[1.02] active:scale-95 shadow-lg">
                            {{ __('ui.game.ready_button') }}
                        </button>
                    </div>
                @endif
            </div>

        @elseif($state->phase === 'finished')
            <div wire:poll.3s="checkGameState">
                <x-end-game-screen
                    :winningFaction="$state->data['winning_faction'] ?? 'no_one'"
                    :players="$players"
                    :currentPlayerId="$player->id"
                />
            </div>

        @elseif($paused)
            {{-- Paused -- do nothing --}}

        @else
            {{-- Normal game --}}

            @php $lastNightDeaths = $state->data['last_night_deaths'] ?? []; @endphp

            @if(!empty($lastNightDeaths) && in_array($state->phase, ['day', 'voting']))
                <div class="w-full max-w-md animate-slideUpReveal">
                    <div class="glass-panel border border-border-default p-4 text-center">
                        <p class="text-text-muted text-xs uppercase tracking-widest mb-2">{{ __('ui.game.last_night') }}</p>
                        <div class="space-y-1">
                            @foreach($lastNightDeaths as $name)
                                <p class="text-text-muted text-sm font-semibold animate-slideInDown" style="animation-delay: {{ $loop->index * 100 }}ms;">{{ $name }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @php
                $mySeerResult = $state->data['seer_results'][$player->id] ?? null;
                $myFoxResult = $state->data['fox_results'][$player->id] ?? null;
                $hasNightResults = $mySeerResult || $myFoxResult;
            @endphp

            <div class="w-full max-w-md space-y-3">
                @if($pendingHunterAction)
                    {{-- HUNTER FINAL ACTION --}}
                    <div class="glass-panel border border-accent-gold/30 p-5 animate-fadeInUp"
                         x-data="{ hunterTimer: 30 }"
                         x-init="setInterval(() => { if (hunterTimer > 0) hunterTimer--; else $wire.resolveHunterTimeout(); }, 1000)">
                        <div class="text-center space-y-3 mb-4">
                            <div class="text-3xl animate-floatSlow">🏹</div>
                            <p class="text-text-primary font-semibold">{{ __('ui.hunter.final_action_title') }}</p>
                            <p class="text-text-muted text-xs">{{ __('ui.hunter.final_action_subtitle') }}</p>
                            <p class="text-accent-gold font-mono text-sm" x-text="hunterTimer + 's'"></p>
                        </div>
                        <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                            @foreach($hunterAlivePlayers as $p)
                                <button wire:click="submitHunterAction('{{ $p->id }}')"
                                        wire:confirm="{{ __('ui.hunter.confirm_shoot') }}"
                                        class="w-full px-4 py-3 bg-bg-surface/50 text-text-primary rounded-lg hover:bg-bg-elevated hover:border-accent-gold/30 border border-border-default transition-all duration-200 text-start flex items-center gap-3 group">
                                    <div class="w-8 h-8 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary group-hover:text-accent-gold transition-colors">
                                        {{ strtoupper(substr($p['nickname'], 0, 1)) }}
                                    </div>
                                    <span class="font-medium">{{ $p['nickname'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                @elseif(!$player->is_alive)
                    <div class="glass-panel border border-border-default p-6 text-center animate-slideUpReveal">
                        <div class="text-5xl mb-3 animate-floatSlow">💀</div>
                        <p class="text-text-muted font-semibold text-lg">{{ __('ui.game.you_are_dead') }}</p>
                        <p class="text-text-muted/60 text-xs mt-2">{{ __('ui.game.you_are_dead_subtitle') }}</p>
                    </div>

                @elseif($state->phase === 'night' && !$player->is_narrator)
                    {{-- Night progress bar --}}
                    @if($nightProgressTotal > 0)
                        <div class="w-full max-w-md glass-panel border border-border-default p-3 animate-fadeInUp">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-text-muted">{{ __('ui.night.night_progress') }}</span>
                                <span class="font-mono font-semibold {{ $nightProgressDone >= $nightProgressTotal ? 'text-accent-green' : 'text-accent-gold' }}">
                                    {{ $nightProgressDone }}/{{ $nightProgressTotal }}
                                </span>
                            </div>
                            <div class="h-1.5 bg-bg-surface rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 ease-out
                                            {{ $nightProgressDone >= $nightProgressTotal ? 'bg-accent-green' : 'bg-accent-gold' }}"
                                     style="width: {{ ($nightProgressDone / $nightProgressTotal) * 100 }}%">
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2">
                                @foreach($nightProgress as $roleKey => $rp)
                                    @php
                                        $emoji = match($roleKey) {
                                            'werewolf' => '🐺', 'big_bad_wolf' => '🐺', 'accursed_wolf_father' => '🐺',
                                            'white_werewolf' => '🐺', 'bodyguard' => '🛡️', 'seer' => '🔮',
                                            'witch' => '🧪', 'pied_piper' => '🎵', 'fox' => '🦊',
                                            'cupid' => '💘', 'wolf_hound' => '🐕', default => '❓',
                                        };
                                    @endphp
                                    <span class="text-[10px] flex items-center gap-1 {{ $rp['completed'] ? 'text-accent-green' : 'text-text-muted' }}">
                                        <span>{{ $emoji }}</span>
                                        <span>{{ $rp['done'] }}/{{ $rp['total'] }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Sequential night mode: show "Stay asleep" if not active --}}
                    @if($isSequentialNight && $player->role && $player->role->night_order !== null && $activeNightRole !== $player->role->key)
                        <div class="glass-panel border border-border-default p-8 text-center animate-fadeInUp">
                            <div class="text-5xl mb-4 animate-floatSlow">😴</div>
                            <p class="text-text-primary font-medium text-lg">{{ __('ui.game.stay_asleep') }}</p>
                            <p class="text-text-muted text-sm mt-2">{{ __('ui.game.waiting_night_subtitle') }}</p>
                        </div>
                    @else
                        <livewire:player.night-role-panel :room="$room" :player="$player" :wire:key="'night-panel-'.$player->id" />
                    @endif

                @elseif($state->phase === 'day' && !$player->is_narrator)
                    {{-- DAY PHASE: Living players list --}}
                    @php
                        $dayAlivePlayers = \App\Models\Player::where('room_id', $room->id)
                            ->where('is_alive', true)
                            ->where('is_narrator', false)
                            ->orderBy('nickname')
                            ->get();
                        $dayDeadPlayers = \App\Models\Player::where('room_id', $room->id)
                            ->where('is_alive', false)
                            ->where('is_narrator', false)
                            ->orderBy('nickname')
                            ->get();
                    @endphp
                    <div class="w-full max-w-md glass-panel border border-border-default p-3 animate-fadeInUp">
                        <div class="flex items-center justify-between text-xs mb-2">
                            <span class="text-text-muted font-semibold uppercase tracking-wider">{{ __('ui.game.survivors') }}</span>
                            <span class="text-accent-green font-mono text-xs font-bold">{{ $playersAliveCount }}/{{ $playersTotalCount }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($dayAlivePlayers as $dap)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-accent-green/5 border border-accent-green/20 rounded-full text-[10px] text-text-primary">
                                    <span class="w-1.5 h-1.5 rounded-full bg-accent-green"></span>
                                    {{ $dap->nickname }}
                                </span>
                            @endforeach
                            @if($dayDeadPlayers->isNotEmpty())
                                <div class="w-full my-1 border-t border-border-default/50"></div>
                                @foreach($dayDeadPlayers as $ddp)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-accent-red/5 border border-accent-red/10 rounded-full text-[10px] text-text-muted line-through">
                                        💀 {{ $ddp->nickname }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="w-full max-w-md animate-fadeInUp"
                         x-data="{ revealed: false }"
                         x-on:pointerdown="revealed = true"
                         x-on:pointerup="revealed = false"
                         x-on:pointerleave="revealed = false">
                        <div class="glass-panel border border-border-default p-8 text-center cursor-pointer hover:border-accent-gold/40 transition-all duration-200">
                            <div class="text-5xl mb-4 animate-floatSlow">☀️</div>
                            <p class="text-text-primary font-medium text-lg">{{ __('ui.game.discussion_time') }}</p>
                            <p class="text-text-muted text-sm mt-2">{{ __('ui.game.discussion_subtitle') }}</p>
                            <p class="text-text-muted/40 text-xs mt-3">{{ __('ui.role.hold_to_reveal') }}</p>
                        </div>
                        <div x-show="revealed" x-cloak
                             x-transition:enter="transition-all duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="glass-panel border border-accent-gold/60 p-4 mt-2">
                            @if($hasNightResults)
                                <p class="text-text-muted text-xs uppercase tracking-widest mb-3">{{ __('ui.result.your_results') }}</p>
                                @if($mySeerResult)
                                    <div class="flex justify-between items-start mb-2 p-2 bg-bg-surface/50 rounded">
                                        <div>
                                            <p class="text-text-muted text-xs">{{ $mySeerResult['target_nickname'] }}</p>
                                            <p class="text-text-primary text-sm">
                                                {{ __('ui.result.faction_label') }}: <span class="text-accent-gold">{{ __("ui.factions.{$mySeerResult['faction']}") }}</span>
                                            </p>
                                        </div>
                                        <button wire:click="dismissResult('seer')"
                                                class="text-text-muted hover:text-accent-gold text-lg leading-none">&times;</button>
                                    </div>
                                @endif
                                @if($myFoxResult)
                                    <div class="flex justify-between items-start p-2 bg-bg-surface/50 rounded">
                                        <p class="text-text-primary text-sm">
                                            {{ $myFoxResult['werewolf_found'] ? '🐺 '.__('ui.result.wolves_found') : '🦊 '.__('ui.result.no_wolves_found') }}
                                        </p>
                                        <button wire:click="dismissResult('fox')"
                                                class="text-text-muted hover:text-accent-gold text-lg leading-none">&times;</button>
                                    </div>
                                @endif
                            @else
                                <p class="text-text-muted text-sm">{{ __('ui.result.no_results') }}</p>
                            @endif
                        </div>
                    </div>

                @elseif($state->phase === 'voting' && !$player->is_narrator)
                    @php
                        $data = $state->data ?? [];
                        $isScapegoatDecreePending = !empty($data['scapegoat_decree_pending']) && ($data['scapegoat_decree_player_id'] ?? null) === $player->id;
                        $isStutteringJudge = $player->role && $player->role->key === 'stuttering_judge';
                        $judgeCanTrigger = $isStutteringJudge && empty($data['stuttering_judge_used']);
                        $isDevotedServant = $player->role && $player->role->key === 'devoted_servant';
                        $swapPending = !empty($data['devoted_servant_swap_pending']) && $isDevotedServant;
                    @endphp
                    @if($swapPending)
                        <div class="glass-panel border border-accent-purple/30 p-5 animate-fadeInUp">
                            <div class="space-y-4">
                                <div class="text-center">
                                    <div class="text-3xl mb-2">🔄</div>
                                    <p class="text-text-primary font-semibold">{{ __('ui.devoted_servant.swap_title') }}</p>
                                    <p class="text-text-muted text-xs mt-1">{{ __('ui.devoted_servant.swap_prompt') }}</p>
                                </div>
                                @php
                                    $swapTargetId = $data['devoted_servant_swap_target_id'] ?? null;
                                    $swapTarget = $swapTargetId ? \App\Models\Player::find($swapTargetId) : null;
                                @endphp
                                @if($swapTarget)
                                    <div class="bg-bg-surface/50 border border-border-default rounded-lg p-3 text-center">
                                        <p class="text-text-muted text-xs">{{ __('ui.devoted_servant.swap_target') }}</p>
                                        <p class="text-text-primary font-semibold mt-1">{{ $swapTarget->nickname }}</p>
                                    </div>
                                @endif
                                <div class="flex gap-3">
                                    <button wire:click="declineSwap"
                                            class="flex-1 py-3 bg-bg-surface border border-border-default text-text-muted font-semibold rounded-lg hover:bg-bg-elevated transition-all duration-200 text-sm">
                                        {{ __('ui.devoted_servant.decline_swap') }}
                                    </button>
                                    <button wire:click="acceptSwap"
                                            wire:confirm="{{ __('ui.devoted_servant.swap_title') }}"
                                            class="flex-1 py-3 bg-accent-purple text-white font-bold rounded-lg hover:bg-accent-purple/90 transition-all duration-200 text-sm shadow-lg hover:scale-[1.02] active:scale-95">
                                        {{ __('ui.devoted_servant.accept_swap') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @elseif($isScapegoatDecreePending && !$scapegoatDecreeSubmitted)
                        <div class="glass-panel border border-accent-gold/30 p-5 animate-fadeInUp">
                            <div class="space-y-4">
                                <div class="text-center">
                                    <div class="text-3xl mb-2">🐐</div>
                                    <p class="text-text-primary font-semibold">{{ __('ui.scapegoat.decree_title') }}</p>
                                    <p class="text-text-muted text-xs mt-1">{{ __('ui.scapegoat.decree_prompt') }}</p>
                                </div>
                                <div class="space-y-1.5 max-h-64 overflow-y-auto scrollbar-thin">
                                    @php
                                        $decreeTargets = \App\Models\Player::where('room_id', $room->id)
                                            ->where('is_alive', true)
                                            ->where('is_narrator', false)
                                            ->where('id', '!=', $player->id)
                                            ->orderBy('nickname')
                                            ->get();
                                    @endphp
                                    @foreach($decreeTargets as $dt)
                                        @php $isBanned = in_array($dt->id, $scapegoatDecreeBanned); @endphp
                                        <button wire:click="toggleDecreeBan('{{ $dt->id }}')"
                                                class="w-full px-4 py-2.5 rounded-lg text-start flex items-center gap-3 transition-all duration-200
                                                       {{ $isBanned
                                                           ? 'bg-accent-red/10 border border-accent-red/40'
                                                           : 'bg-bg-surface/50 border border-border-default hover:bg-bg-elevated' }}">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center text-xs transition-colors
                                                        {{ $isBanned ? 'bg-accent-red border-accent-red text-white' : 'border-border-default' }}">
                                                @if($isBanned) ✕ @endif
                                            </div>
                                            <span class="text-sm {{ $isBanned ? 'text-accent-red line-through' : 'text-text-primary' }}">{{ $dt->nickname }}</span>
                                        </button>
                                    @endforeach
                                </div>
                                <button wire:click="submitDecree"
                                        class="w-full py-3 bg-accent-gold text-bg-primary font-bold rounded-lg hover:bg-accent-gold-dark transition-all duration-200 text-sm shadow-lg hover:scale-[1.02] active:scale-95">
                                    {{ __('ui.scapegoat.submit_decree') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <livewire:player.voting-panel :room="$room" :player="$player" :wire:key="'voting-'.$player->id" />
                        @if($judgeCanTrigger)
                            <div class="glass-panel border border-accent-purple/30 p-3 mt-2">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">⚖️</span>
                                        <span class="text-xs text-accent-purple font-semibold">{{ __('ui.stuttering_judge.trigger_title') }}</span>
                                    </div>
                                    <button wire:click="triggerSecondVote"
                                            wire:confirm="{{ __('ui.stuttering_judge.confirm_trigger') }}"
                                            class="px-3 py-1.5 bg-accent-purple text-white text-xs font-semibold rounded-lg hover:bg-accent-purple/90 transition-colors">
                                        {{ __('ui.stuttering_judge.trigger_button') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif

                {{-- READY BUTTON (for all phases except waiting/finished/dead) --}}
                @if(!in_array($state->phase, ['waiting', 'finished']) && $player->is_alive && !$pendingHunterAction)
                    @if($state->phase === 'night' && $isSequentialNight && $player->role && $player->role->night_order !== null && $activeNightRole !== $player->role->key)
                        {{-- Sequential mode: show ready button even when not active --}}
                        <div class="w-full max-w-md mt-4">
                            <div class="glass-panel border border-border-default p-4 text-center">
                                <p class="text-text-muted text-xs mb-3">{{ __('ui.game.waiting_night_action') }}</p>
                                <button wire:click="readyUp"
                                        class="w-full py-3 rounded-xl font-semibold transition-all duration-200 text-sm shadow-lg
                                               {{ $ready
                                                   ? 'bg-accent-green/20 text-accent-green border border-accent-green/40'
                                                   : 'bg-bg-surface/50 text-text-secondary border border-border-default hover:bg-bg-elevated hover:border-accent-gold/30' }}">
                                    {{ $ready ? '✓ '.__('ui.game.ready') : __('ui.game.mark_ready') }}
                                </button>
                            </div>
                        </div>
                    @elseif($state->phase === 'night')
                        <div class="w-full max-w-md mt-4">
                            <button wire:click="readyUp"
                                    class="w-full py-3 rounded-xl font-semibold transition-all duration-200 text-sm shadow-lg
                                           {{ $ready
                                               ? 'bg-accent-green/20 text-accent-green border border-accent-green/40'
                                               : 'bg-bg-surface/50 text-text-secondary border border-border-default hover:bg-bg-elevated hover:border-accent-gold/30' }}">
                                {{ $ready ? '✓ '.__('ui.game.ready') : __('ui.game.mark_ready') }}
                            </button>
                        </div>
                    @elseif($state->phase === 'day')
                        <div class="w-full max-w-md mt-4">
                            <button wire:click="readyUp"
                                    class="w-full py-3 rounded-xl font-semibold transition-all duration-200 text-sm shadow-lg
                                           {{ $ready
                                               ? 'bg-accent-green/20 text-accent-green border border-accent-green/40'
                                               : 'bg-bg-surface/50 text-text-secondary border border-border-default hover:bg-bg-elevated hover:border-accent-gold/30' }}">
                                {{ $ready ? '✓ '.__('ui.game.ready_to_vote') : __('ui.game.mark_ready_to_vote') }}
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>
