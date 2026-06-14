<div class="relative w-full max-w-[260px] sm:max-w-xs mx-auto cursor-pointer select-none"
     style="perspective: 1000px; touch-action: manipulation;"
     x-data="{
         revealed: false,
         holding: false,
         holdTimer: null,
          startHold() {
              this.holding = true;
              this.holdTimer = setTimeout(() => {
                  this.revealed = true;
                  $wire.reveal();
                  if (navigator.vibrate) navigator.vibrate(30);
              }, 800);
          },
          endHold() {
              this.holding = false;
              if (this.holdTimer) {
                  clearTimeout(this.holdTimer);
                  this.holdTimer = null;
              }
              if (this.revealed) {
                  this.revealed = false;
                  $wire.hide();
              }
          },
         closeRevealed() {
             this.revealed = false;
             $wire.hide();
         }
     }"
     x-on:pointerdown="startHold()"
     x-on:pointerup="endHold()"
     x-on:pointerleave="endHold()"
     x-on:pointercancel="endHold()"
     x-on:touchcancel="endHold()">

    <div class="relative w-full aspect-[3/4] transition-transform duration-500"
         :class="revealed ? 'rotate-y-180' : ''"
         style="transform-style: preserve-3d;">

        {{-- Masked face - IDENTICAL for all roles --}}
        <div class="absolute inset-0 backface-hidden rounded-xl card-masked flex items-center justify-center animate-cardGlow">
            <div class="text-center space-y-3 p-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full bg-gradient-to-br from-accent-gold/20 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center"
                     :class="holding ? 'scale-110 border-accent-gold/60' : ''"
                     style="transition: all 0.3s ease;">
                    <div class="text-3xl sm:text-4xl" :class="holding ? 'text-accent-gold' : 'text-accent-gold/50'">🎭</div>
                </div>
                <p class="text-text-muted text-xs sm:text-sm tracking-widest uppercase font-medium">{{ __('ui.role.secret_role') }}</p>
                <p class="text-text-muted/50 text-xs">{{ __('ui.role.hold_to_reveal') }}</p>
                <p class="text-text-muted/30 text-[10px]">{{ __('ui.role.keep_private') }}</p>
            </div>
        </div>

        {{-- Revealed face - shows role details --}}
        <div class="absolute inset-0 backface-hidden rounded-xl card-revealed p-4 sm:p-5 flex flex-col items-center justify-center rotate-y-180">
            {{-- Close button --}}
            <button @click="closeRevealed()"
                    class="absolute top-2 right-2 text-text-muted hover:text-text-primary text-lg leading-none px-2 py-1 rounded hover:bg-bg-elevated transition-colors z-10">
                &times;
            </button>
            <div class="flex-1 flex flex-col items-center justify-center space-y-2 sm:space-y-3">
                @if($roleData)
                    <div class="text-4xl sm:text-5xl animate-floatSlow">
                        @switch($roleData['key'])
                            @case('villager') 🏘️ @break
                            @case('seer') 👁️ @break
                            @case('witch') 🧙 @break
                            @case('hunter') 🏹 @break
                            @case('bodyguard') 🛡️ @break
                            @case('little_girl') 👧 @break
                            @case('cupid') 💘 @break
                            @case('elder') 👑 @break
                            @case('scapegoat') 🐐 @break
                            @case('village_idiot') 🤡 @break
                            @case('two_sisters') 👭 @break
                            @case('three_brothers') 👬 @break
                            @case('stuttering_judge') ⚖️ @break
                            @case('knight_with_rusty_sword') ⚔️ @break
                            @case('devoted_servant') 🤝 @break
                            @case('bear_tamer') 🐻 @break
                            @case('fox') 🦊 @break
                            @case('werewolf') 🐺 @break
                            @case('big_bad_wolf') 🐾 @break
                            @case('accursed_wolf_father') 🦇 @break
                            @case('white_werewolf') 🌕 @break
                            @case('wolf_hound') 🐕 @break
                            @case('pied_piper') 🎵 @break
                            @case('angel') 😇 @break
                            @default ❓ @endswitch
                    </div>
                    <h2 class="text-xl sm:text-2xl font-serif font-bold text-text-primary text-center">
                        {{ $roleData['name'] }}
                    </h2>
                    <p class="text-text-muted text-xs sm:text-sm text-center max-w-[14rem] sm:max-w-[16rem] leading-relaxed">
                        {{ $roleData['description'] }}
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $roleData['faction'] === 'werewolves' ? 'bg-accent-red/20 text-accent-red' :
                               ($roleData['faction'] === 'village' ? 'bg-accent-blue/20 text-accent-blue' :
                               'bg-accent-purple/20 text-accent-purple') }}">
                            {{ __("ui.factions.{$roleData['faction']}") }}
                        </span>
                        @if($roleData['has_night_action'])
                            <span class="text-xs px-2 py-0.5 rounded-full bg-accent-gold/20 text-accent-gold">
                                {{ __('ui.role.night_order') }}: {{ $roleData['night_order'] }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Hold progress indicator --}}
    <div x-show="holding" x-cloak
         class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-16 h-1 bg-bg-elevated rounded-full overflow-hidden">
        <div class="h-full bg-accent-gold rounded-full"
             style="transition: width 0.8s linear;"
             :style="holding ? 'width: 100%' : 'width: 0%'"></div>
    </div>
</div>
