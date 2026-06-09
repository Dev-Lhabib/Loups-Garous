<div wire:poll.5s class="min-h-screen flex flex-col p-4 md:p-8"
     x-data="{
         showOverlay: false,
         phaseLabel: '',
         phaseClass: '',
         resultNotif: null,
         resultTimer: null,
         nightEliminated: null,
         nightTimer: null,
         showResults: false,
     }"
     @transition-phase.window="
         showOverlay = true;
         phaseLabel = $event.detail.label;
         phaseClass = $event.detail.class;
         setTimeout(() => { showOverlay = false; }, 1500);
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
>
    {{-- Phase transition overlay --}}
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
        <h2 class="text-4xl font-serif font-bold text-text-primary" x-text="phaseLabel"></h2>
    </div>

    {{-- Result notification --}}
    <div x-show="resultNotif" x-cloak
         class="fixed top-4 left-1/2 -translate-x-1/2 z-50 glass-panel border-2 rounded-xl px-6 py-4 shadow-2xl max-w-md w-full text-center animate-slideInDown"
         :class="{
             'border-accent-gold/60': resultNotif?.type === 'seer' || resultNotif?.type === 'fox',
             'border-accent-red/60': resultNotif?.type === 'night_resolved' || resultNotif?.type === 'lover_died',
             'border-accent-gold/60': resultNotif?.type === 'village_idiot',
         }"
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
                <p class="text-accent-red text-lg font-bold">{{ __('ui.result.lover_died_title') }}</p>
                <p class="text-text-primary mt-1">
                    <span class="text-accent-pink font-semibold" x-text="resultNotif.partner_nickname"></span>
                    {{ __('ui.result.lover_died_text') }}
                </p>
            </div>
        </template>

        {{-- Village Idiot revealed --}}
        <template x-if="resultNotif?.type === 'village_idiot'">
            <div>
                <p class="text-accent-gold text-lg font-bold">{{ __('ui.result.idiot_revealed') }}</p>
                <p class="text-text-primary mt-1" x-text="resultNotif.nickname"></p>
            </div>
        </template>
    </div>

    {{-- Night resolved notification --}}
    <div x-show="nightEliminated" x-cloak
         class="fixed top-24 left-1/2 -translate-x-1/2 z-40 glass-panel border-2 border-accent-red/60 rounded-xl px-6 py-4 shadow-2xl max-w-md w-full text-center animate-slideInDown"
         x-transition:leave="transition-all duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="text-3xl mb-2">🌅</div>
        <p class="text-text-primary text-lg font-bold">{{ __('ui.result.night_eliminations') }}</p>
        <div class="mt-2 space-y-1">
            <template x-for="name in (nightEliminated?.eliminated || [])" :key="name">
                <p class="text-accent-red font-semibold" x-text="name"></p>
            </template>
        </div>
        <p x-show="nightEliminated && nightEliminated.eliminated.length === 0" class="text-text-muted italic mt-2">
            {{ __('ui.result.no_deaths') }}
        </p>
    </div>

    <div class="flex-1 flex flex-col items-center justify-center space-y-6 w-full max-w-2xl mx-auto">

        {{-- Phase Header --}}
        <x-phase-header
            :phase="$state->phase"
            :round="$state->round"
            :aliveCount="$state->phase !== 'finished' && $state->phase !== 'waiting' ? ($player->is_alive ? 1 : 0) : 0"
            :totalCount="0"
            :roomCode="$room->code"
        />

        @if($state->phase === 'waiting')
            {{-- Ready phase --}}
            <livewire:player.role-card :player="$player" :wire:key="'role-'.$player->id" />

            <div class="w-full max-w-md">
                @if($ready)
                    <div class="glass-panel border border-accent-green/50 p-6 text-center">
                        <div class="text-3xl mb-2 text-accent-green">✓</div>
                        <p class="text-text-muted">{{ __('ui.game.waiting_for_others') }}</p>
                        <div class="flex justify-center gap-1.5 mt-3">
                            <span class="w-2 h-2 rounded-full bg-accent-green animate-pulse animation-delay-200"></span>
                            <span class="w-2 h-2 rounded-full bg-accent-green animate-pulse animation-delay-400"></span>
                            <span class="w-2 h-2 rounded-full bg-accent-green animate-pulse animation-delay-600"></span>
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
            {{-- Game Over --}}
            @php $winningFaction = $state->data['winning_faction'] ?? 'no_one'; @endphp
            <div class="w-full max-w-lg text-center space-y-4">
                <div class="glass-panel border-2 border-accent-gold/40 p-8">
                    @php
                        $winIcons = ['village' => '🏘️', 'werewolves' => '🐺', 'white_werewolf' => '🌕', 'pied_piper' => '🎵', 'angel' => '😇', 'lovers' => '💕', 'no_one' => '💀'];
                        $winIcon = $winIcons[$winningFaction] ?? '❓';
                    @endphp
                    <div class="text-5xl mb-4">{{ $winIcon }}</div>
                    <h2 class="text-2xl font-serif font-bold text-accent-gold">{{ __('ui.game.over') }}</h2>
                    <p class="text-lg mt-2 text-text-primary">{{ __("ui.win.{$winningFaction}") }}</p>
                </div>

                {{-- Role reveal --}}
                <div class="glass-panel border border-border-default p-5">
                    <h3 class="text-sm font-semibold text-text-primary mb-4">{{ __('ui.game.role_reveal') }}</h3>
                    <div class="space-y-2">
                        @foreach($players as $p)
                            @php
                                $factionColors = [
                                    'village' => 'border-l-accent-blue', 'werewolves' => 'border-l-accent-red',
                                    'white_werewolf' => 'border-l-accent-purple', 'pied_piper' => 'border-l-accent-green',
                                    'angel' => 'border-l-accent-gold', 'lovers' => 'border-l-accent-pink',
                                ];
                                $fc = $factionColors[$p->role?->faction ?? ''] ?? 'border-l-border-default';
                            @endphp
                            <div class="flex items-center justify-between px-3 py-2.5 bg-bg-surface/50 rounded-lg border-l-2 {{ $fc }}
                                        {{ $p->is_alive ? '' : 'opacity-50' }}">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-bg-elevated flex items-center justify-center text-xs font-bold text-text-secondary">
                                        {{ strtoupper(substr($p->nickname, 0, 1)) }}
                                    </div>
                                    <span class="text-sm text-text-primary {{ !$p->is_alive ? 'line-through text-text-muted' : '' }}">
                                        {{ $p->nickname }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-role-icon :roleKey="$p->role?->key ?? ''" class="text-xs" />
                                    <span class="text-xs text-text-muted">{{ $p->role ? __("roles.{$p->role->key}.name") : '?' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <p class="text-text-muted text-xs italic">{{ __('ui.narrator.waiting_for_new_game') }}</p>
            </div>

        @else
            {{-- Normal game --}}
            <livewire:player.role-card :player="$player" :wire:key="'role-'.$player->id" />

            @php $lastNightDeaths = $state->data['last_night_deaths'] ?? []; @endphp

            @if(!empty($lastNightDeaths) && in_array($state->phase, ['day', 'voting']))
                <div class="w-full max-w-md animate-fadeInUp">
                    <div class="glass-panel border border-accent-red/40 p-4 text-center">
                        <p class="text-text-muted text-xs uppercase tracking-widest mb-2">{{ __('ui.game.last_night') }}</p>
                        <div class="space-y-1">
                            @foreach($lastNightDeaths as $name)
                                <p class="text-accent-red text-sm font-semibold">{{ $name }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @php
                $mySeerResult = $state->data['seer_results'][$player->id] ?? null;
                $myFoxResult = $state->data['fox_results'][$player->id] ?? null;
            @endphp

            @if(($mySeerResult || $myFoxResult) && in_array($state->phase, ['day', 'voting']))
                <div class="w-full max-w-md animate-fadeInUp"
                     x-data="{ revealed: false }"
                     x-on:mousedown="revealed = true"
                     x-on:mouseup="revealed = false"
                     x-on:mouseleave="revealed = false"
                     x-on:touchstart="revealed = true"
                     x-on:touchend="revealed = false">
                    <div x-show="!revealed"
                         class="glass-panel border border-border-default p-6 text-center cursor-pointer">
                        <p class="text-text-muted">{{ __('ui.game.discussion_time') }}</p>
                    </div>
                    <div x-show="revealed" x-cloak
                         class="glass-panel border border-accent-gold/60 p-4">
                        <p class="text-accent-gold text-xs uppercase tracking-widest mb-3">{{ __('ui.result.your_results') }}</p>
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
                    </div>
                </div>
            @endif

            <div class="w-full max-w-md">
                @if(!$player->is_alive)
                    <div class="glass-panel border border-accent-red/50 p-6 text-center">
                        <div class="text-3xl mb-2">💀</div>
                        <p class="text-accent-red font-semibold">{{ __('ui.game.you_are_dead') }}</p>
                    </div>
                @elseif($state->phase === 'night' && !$player->is_narrator)
                    <livewire:player.night-action :room="$room" :player="$player" :wire:key="'night-action-'.$player->id" />
                @elseif($state->phase === 'voting' && !$player->is_narrator)
                    <livewire:player.voting-panel :room="$room" :player="$player" :wire:key="'voting-'.$player->id" />
                @elseif($state->phase === 'day' && !$mySeerResult && !$myFoxResult)
                    <div class="glass-panel border border-border-default p-8 text-center">
                        <div class="text-4xl mb-3">☀️</div>
                        <p class="text-text-primary font-medium">{{ __('ui.game.discussion_time') }}</p>
                        <p class="text-text-muted text-sm mt-2">{{ __('ui.game.look_around') }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
