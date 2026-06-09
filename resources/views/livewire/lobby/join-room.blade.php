<div class="min-h-screen flex flex-col items-center justify-center px-6 py-12">
    <div class="w-full max-w-sm mx-auto text-center space-y-8 animate-fadeInUp">

        <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-accent-gold/30 to-accent-gold/5 border-2 border-accent-gold/30 flex items-center justify-center animate-heartbeat">
            <span class="text-3xl">👋</span>
        </div>

        <div class="space-y-2">
            <h1 class="font-serif text-3xl font-bold text-text-primary">{{ __('ui.lobby.join_room') }}</h1>
            <p class="text-text-muted text-sm">{{ __('ui.home.subtitle') }}</p>
        </div>

        <form wire:submit="submit" class="space-y-4">
            <div class="text-left">
                <label for="code" class="block text-sm text-text-muted mb-1.5 font-medium">{{ __('ui.lobby.room_code') }}</label>
                <input type="text"
                       id="code"
                       wire:model="code"
                       placeholder="{{ __('ui.lobby.code_placeholder') }}"
                       maxlength="6"
                       class="w-full px-4 py-3 bg-bg-surface border border-border-default rounded-xl text-text-primary placeholder:text-text-muted/50 focus:border-accent-gold/50 focus:ring-1 focus:ring-accent-gold/30 outline-none transition-all duration-200 text-center font-mono text-lg uppercase tracking-widest"/>
                @error('code') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="text-left">
                <label for="nickname" class="block text-sm text-text-muted mb-1.5 font-medium">{{ __('ui.lobby.your_nickname') }}</label>
                <input type="text"
                       id="nickname"
                       wire:model="nickname"
                       placeholder="{{ __('ui.lobby.nickname_placeholder') }}"
                       maxlength="20"
                       class="w-full px-4 py-3 bg-bg-surface border border-border-default rounded-xl text-text-primary placeholder:text-text-muted/50 focus:border-accent-gold/50 focus:ring-1 focus:ring-accent-gold/30 outline-none transition-all duration-200"/>
                @error('nickname') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                    class="w-full py-4 px-6 bg-accent-gold text-bg-primary font-bold rounded-xl hover:bg-accent-gold-dark transition-all duration-200 hover:scale-[1.02] active:scale-95 shadow-lg text-center">
                {{ __('ui.button.join_room') }}
            </button>

            <a href="{{ route('rooms.create') }}" class="block text-sm text-text-muted hover:text-accent-gold transition-colors">
                {{ __('ui.button.create_room') }}
            </a>
        </form>
    </div>
</div>
