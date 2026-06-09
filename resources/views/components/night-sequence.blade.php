@props([
    'nightOrder' => [],
    'pendingRoles' => [],
    'submittedRoles' => [],
    'currentPhase' => '',
])

@php
$roleLabels = [];
foreach ($nightOrder as $key) {
    $roleLabels[$key] = __("roles.{$key}.name");
}

$sequence = [
    ['key' => 'cupid', 'icon' => '💘'],
    ['key' => 'wolf_hound', 'icon' => '🐕'],
    ['key' => 'werewolf', 'icon' => '🐺'],
    ['key' => 'big_bad_wolf', 'icon' => '🐾'],
    ['key' => 'accursed_wolf_father', 'icon' => '🦇'],
    ['key' => 'white_werewolf', 'icon' => '🌕'],
    ['key' => 'bodyguard', 'icon' => '🛡️'],
    ['key' => 'little_girl', 'icon' => '👧'],
    ['key' => 'seer', 'icon' => '👁️'],
    ['key' => 'witch', 'icon' => '🧙'],
    ['key' => 'pied_piper', 'icon' => '🎵'],
    ['key' => 'fox', 'icon' => '🦊'],
    ['key' => 'bear_tamer', 'icon' => '🐻'],
];

$activeRoles = collect($sequence)->filter(fn($s) => in_array($s['key'], $nightOrder))->values();
@endphp

<div class="glass-panel border border-border-default p-4">
    <h3 class="text-text-primary text-sm font-semibold mb-3 flex items-center gap-2">
        <span>🌙</span>
        {{ __('ui.narrator.night_sequence') }}
    </h3>
    <div class="space-y-1">
        @php $foundCurrent = false; @endphp
        @foreach($activeRoles as $i => $step)
            @php
                $isPending = in_array($step['key'], $pendingRoles);
                $isSubmitted = in_array($step['key'], $submittedRoles);
                $isActive = $isPending && !$isSubmitted;
                $isDone = $isSubmitted;
                $isUpcoming = !$isPending && !$isSubmitted;
                if ($isActive && !$foundCurrent) {
                    $foundCurrent = true;
                } elseif ($isActive && $foundCurrent) {
                    $isActive = false;
                    $isUpcoming = true;
                }
            @endphp
            <div class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all duration-300
                {{ $isActive ? 'bg-accent-blue/10 border border-accent-blue/30' : '' }}
                {{ $isDone ? 'opacity-60' : '' }}
                {{ $isUpcoming ? 'opacity-40' : '' }}">
                <div class="flex-shrink-0 w-5 text-center text-sm">{{ $step['icon'] }}</div>
                <div class="flex-1 text-xs truncate {{ $isActive ? 'text-accent-blue font-medium' : ($isDone ? 'text-text-secondary' : 'text-text-muted') }}">
                    {{ $roleLabels[$step['key']] ?? $step['key'] }}
                </div>
                <div class="flex-shrink-0">
                    @if($isDone)
                        <span class="text-accent-green text-xs">✓</span>
                    @elseif($isActive)
                        <span class="w-2 h-2 rounded-full bg-accent-blue animate-pulse inline-block"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-text-muted/30 inline-block"></span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
