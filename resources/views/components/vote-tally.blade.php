@props([
    'tally' => [],
    'voteCount' => 0,
    'totalVoters' => 0,
    'players' => collect(),
])

<div class="glass-panel border border-accent-red/30 p-4">
    <h3 class="text-accent-red text-sm font-semibold mb-3 flex items-center gap-2">
        <span>🗳️</span>
        <span>{{ __('ui.vote.ongoing') }}</span>
        <span class="text-xs text-text-muted ms-auto bg-accent-red/10 px-1.5 py-0.5 rounded-full font-mono">
            {{ $voteCount }}/{{ $totalVoters }}
        </span>
    </h3>

    @if($totalVoters > 0)
        <div class="mb-2 h-1.5 bg-bg-surface rounded-full overflow-hidden">
            <div class="h-full bg-accent-red rounded-full transition-all duration-500"
                 style="width: {{ $totalVoters > 0 ? ($voteCount / $totalVoters) * 100 : 0 }}%">
            </div>
        </div>
    @endif

    <div class="space-y-1 max-h-56 overflow-y-auto scrollbar-thin">
        @forelse($tally as $targetId => $count)
            @php $maxCount = max(($tally ? max($tally) : 1), 1); @endphp
            <div class="group relative">
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-bg-surface/50 text-sm relative z-10">
                    <span class="text-text-primary truncate me-2">{{ $players->firstWhere('id', $targetId)?->nickname ?? "Player #{$targetId}" }}</span>
                    <span class="text-accent-red font-mono text-xs font-bold">{{ $count }}</span>
                </div>
                <div class="absolute inset-0 rounded-lg bg-accent-red/5 transition-all duration-300"
                     style="width: {{ ($count / $maxCount) * 100 }}%">
                </div>
            </div>
        @empty
            <p class="text-text-muted text-xs text-center py-6 italic">{{ __('ui.vote.no_votes_yet') }}</p>
        @endforelse
    </div>
</div>
