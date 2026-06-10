<div class="relative w-full max-w-[260px] sm:max-w-xs mx-auto cursor-pointer select-none"
     style="perspective: 1000px;"
     x-data="{ revealed: false }"
     x-on:mousedown="revealed = true"
     x-on:mouseup="revealed = false"
     x-on:mouseleave="revealed = false"
     x-on:touchstart="revealed = true"
     x-on:touchend="revealed = false">

    <div class="relative w-full aspect-[3/4] transition-transform duration-500"
         :class="revealed ? 'rotate-y-180' : ''"
         style="transform-style: preserve-3d;">
        {{-- Masked face - identical for all roles --}}
        <div class="absolute inset-0 backface-hidden rounded-xl card-masked flex items-center justify-center animate-cardGlow">
            <div class="text-center space-y-3 p-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full bg-gradient-to-br from-accent-gold/20 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center">
                    <div class="text-3xl sm:text-4xl text-accent-gold/50 font-serif font-bold">?</div>
                </div>
                <p class="text-text-muted text-xs sm:text-sm tracking-widest uppercase font-medium">{{ __('ui.role.hold_to_reveal') }}</p>
                <p class="text-text-muted/50 text-xs">{{ $player->nickname }}</p>
            </div>
        </div>

        {{-- Revealed face - identical styling for ALL roles --}}
        <div class="absolute inset-0 backface-hidden rounded-xl card-revealed p-4 sm:p-6 flex flex-col items-center justify-center rotate-y-180">
            <div class="flex-1 flex flex-col items-center justify-center space-y-3 sm:space-y-4">
                <x-role-icon :roleKey="$role->key" class="text-4xl sm:text-5xl animate-floatSlow" />
                <h2 class="text-xl sm:text-2xl font-serif font-bold text-text-primary text-center">
                    {{ __("roles.{$role->key}.name") }}
                </h2>
                <p class="text-text-muted text-xs sm:text-sm text-center max-w-[14rem] sm:max-w-[16rem] leading-relaxed">
                    {{ __("roles.{$role->key}.description") }}
                </p>
                @if($role->night_order !== null)
                    <div class="text-xs text-text-muted bg-bg-elevated/50 px-3 py-1 rounded-full">
                        {{ __('ui.role.night_order') }}: {{ $role->night_order }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>