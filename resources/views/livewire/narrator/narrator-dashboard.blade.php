<div class="min-h-screen"
     x-data="{
         showOverlay: false,
         phaseLabel: '',
         phaseClass: '',
         sidebarTab: 'status',
         showRoleModal: false,
         modalPlayer: null,
     }"
     @transition-phase.window="
         showOverlay = true;
         phaseLabel = $event.detail.label;
         phaseClass = $event.detail.class;
         setTimeout(() => { showOverlay = false; }, 1500);
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
                                    <span :class="modalPlayer.is_alive ? 'text-accent-green' : 'text-accent-red'" x-text="modalPlayer.is_alive ? 'Alive' : 'Dead'"></span>
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

    <div class="max-w-7xl mx-auto p-4 md:p-6 space-y-4">

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
                            class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg
                                   {{ $btnConfig['bg'] }} {{ $btnConfig['hover'] }} {{ $btnConfig['text'] }}
                                   hover:scale-105 active:scale-95">
                        {{ __("ui.phase.go_to_{$target}") }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ===== MAIN CONTENT: 2-COLUMN LAYOUT ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 lg:gap-6">

            {{-- LEFT: Player grid --}}
            <div class="lg:col-span-3 space-y-4">
                {{-- Pending roles banner (night phase) --}}
                @if($state->phase === 'night' && count($pendingRoles) > 0)
                    <div class="glass-panel border border-accent-blue/30 p-3 flex items-center gap-3">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 rounded-full bg-accent-blue animate-pulse animation-delay-200"></span>
                            <span class="w-2 h-2 rounded-full bg-accent-blue animate-pulse animation-delay-400"></span>
                            <span class="w-2 h-2 rounded-full bg-accent-blue animate-pulse animation-delay-600"></span>
                        </div>
                        <span class="text-xs text-accent-blue">{{ __('ui.narrator.pending_actions') }}:</span>
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_unique($pendingRoles) as $roleKey)
                                <span class="text-[10px] bg-accent-blue/10 text-accent-cyan px-1.5 py-0.5 rounded-full">
                                    {{ __("roles.{$roleKey}.name") }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Player grid --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2.5 md:gap-3">
                    @forelse($players as $p)
                        @php
                            $isLover = isset($loverMap[$p->id]);
                            $isEnchanted = in_array($p->id, $enchantedIds);
                            $factionColors = [
                                'village' => 'border-l-accent-blue',
                                'werewolves' => 'border-l-accent-red',
                                'white_werewolf' => 'border-l-accent-purple',
                                'pied_piper' => 'border-l-accent-green',
                                'angel' => 'border-l-accent-gold',
                            ];
                            $borderColor = $factionColors[$p->role?->faction ?? 'village'] ?? 'border-l-border-default';
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
                             class="relative bg-bg-card border border-border-default rounded-xl p-3 cursor-pointer transition-all duration-200 hover:border-accent-gold/40 hover:shadow-lg group
                                    {{ !$p->is_alive ? 'opacity-50 grayscale' : 'hover:glow-gold' }}
                                    border-l-2 {{ $borderColor }}">
                            <div class="flex items-center gap-2.5">
                                <div class="relative flex-shrink-0">
                                    <div class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-bg-elevated flex items-center justify-center text-sm font-bold text-text-secondary">
                                        {{ strtoupper(substr($p->nickname, 0, 2)) }}
                                    </div>
                                    @if(!$p->is_alive)
                                        <div class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-accent-red rounded-full flex items-center justify-center text-[10px]">💀</div>
                                    @elseif($isLover)
                                        <div class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-accent-pink rounded-full flex items-center justify-center text-[8px]">💕</div>
                                    @elseif($isEnchanted)
                                        <div class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-accent-green rounded-full flex items-center justify-center text-[8px]">✦</div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-text-primary text-sm font-medium truncate {{ !$p->is_alive ? 'line-through text-text-muted' : '' }}">
                                        {{ $p->nickname }}
                                    </p>
                                    @if($p->role)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <x-role-icon :roleKey="$p->role->key" class="text-xs" />
                                            <span class="text-text-muted text-[10px] truncate">{{ __("roles.{$p->role->key}.name") }}</span>
                                        </div>
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
                    <div class="flex border-b border-border-default">
                        <button @click="sidebarTab = 'status'"
                                class="flex-1 px-3 py-2.5 text-xs font-medium transition-all duration-200"
                                :class="sidebarTab === 'status' ? 'text-accent-gold border-b-2 border-accent-gold bg-accent-gold/5' : 'text-text-muted hover:text-text-secondary'">
                            📊 {{ __('ui.narrator.tab_status') }}
                        </button>
                        <button @click="sidebarTab = 'night'"
                                class="flex-1 px-3 py-2.5 text-xs font-medium transition-all duration-200"
                                :class="sidebarTab === 'night' ? 'text-accent-blue border-b-2 border-accent-blue bg-accent-blue/5' : 'text-text-muted hover:text-text-secondary'">
                            🌙 {{ __('ui.narrator.tab_night') }}
                        </button>
                        <button @click="sidebarTab = 'log'"
                                class="flex-1 px-3 py-2.5 text-xs font-medium transition-all duration-200"
                                :class="sidebarTab === 'log' ? 'text-accent-gold border-b-2 border-accent-gold bg-accent-gold/5' : 'text-text-muted hover:text-text-secondary'">
                            📜 {{ __('ui.narrator.tab_log') }}
                        </button>
                        <button @click="sidebarTab = 'history'"
                                class="flex-1 px-3 py-2.5 text-xs font-medium transition-all duration-200"
                                :class="sidebarTab === 'history' ? 'text-accent-purple border-b-2 border-accent-purple bg-accent-purple/5' : 'text-text-muted hover:text-text-secondary'">
                            📋 {{ __('ui.narrator.tab_history') }}
                        </button>
                    </div>

                    {{-- Tab Content --}}
                    <div class="p-3 max-h-[calc(100vh-280px)] overflow-y-auto scrollbar-thin">
                        {{-- TAB: Status --}}
                        <div x-show="sidebarTab === 'status'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <div class="space-y-3">
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
                                        <h4 class="text-xs uppercase tracking-wider text-accent-pink font-semibold mb-2">💕 {{ __('ui.game.lovers') }}</h4>
                                        <div class="space-y-1">
                                            @foreach($loverMap as $pid => $partnerId)
                                                @if($pid < $partnerId)
                                                    @php
                                                        $p1 = $players->firstWhere('id', $pid);
                                                        $p2 = $players->firstWhere('id', $partnerId);
                                                    @endphp
                                                    @if($p1 && $p2)
                                                        <div class="text-xs text-text-secondary bg-accent-pink/5 rounded px-2 py-1">
                                                            {{ $p1->nickname }} 💕 {{ $p2->nickname }}
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
                                        <h4 class="text-xs uppercase tracking-wider text-accent-green font-semibold mb-2">✦ {{ __('ui.game.enchanted') }}</h4>
                                        <div class="space-y-1">
                                            @foreach($enchantedIds as $eid)
                                                @php $ep = $players->firstWhere('id', $eid); @endphp
                                                @if($ep)
                                                    <div class="text-xs text-text-secondary bg-accent-green/5 rounded px-2 py-1">
                                                        {{ $ep->nickname }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Alive/Dead player lists --}}
                                <div>
                                    <h4 class="text-xs uppercase tracking-wider text-accent-green font-semibold mb-2">✓ {{ __('ui.game.alive') }} ({{ $alivePlayers->count() }})</h4>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($alivePlayers as $ap)
                                            <span class="text-[11px] bg-accent-green/10 text-accent-green px-2 py-0.5 rounded-full">{{ $ap->nickname }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @if($deadPlayers->isNotEmpty())
                                    <div>
                                        <h4 class="text-xs uppercase tracking-wider text-accent-red font-semibold mb-2">💀 {{ __('ui.game.dead') }} ({{ $deadPlayers->count() }})</h4>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($deadPlayers as $dp)
                                                <span class="text-[11px] bg-accent-red/10 text-accent-red/70 px-2 py-0.5 rounded-full line-through">{{ $dp->nickname }}</span>
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
                                    {{-- Night sequence tracker --}}
                                    @php
                                        $submittedRoles = collect($nightActionFeed)->pluck('role_key')->unique()->values()->toArray();
                                    @endphp
                                    <x-night-sequence
                                        :nightOrder="$nightOrder"
                                        :pendingRoles="$pendingRoles"
                                        :submittedRoles="$submittedRoles"
                                    />

                                    {{-- Night action timeline --}}
                                    <x-night-timeline :actions="$nightActionFeed" />
                                @elseif($state->phase === 'voting')
                                    <x-vote-tally
                                        :tally="$voteTally"
                                        :voteCount="$voteCount"
                                        :totalVoters="$players->where('is_alive', true)->where('voting_banned', false)->count()"
                                        :players="$players"
                                    />
                                @else
                                    <div class="text-center py-8">
                                        <p class="text-text-muted text-xs">{{ __('ui.narrator.no_actions_yet') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB: Game Log --}}
                        <div x-show="sidebarTab === 'log'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <x-game-timeline :entries="$gameLog" />
                        </div>

                        {{-- TAB: Action History --}}
                        <div x-show="sidebarTab === 'history'" x-transition:enter="transition-all duration-200" x-transition:enter-start="opacity-0">
                            <x-action-history :actions="$actionHistory" />
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
