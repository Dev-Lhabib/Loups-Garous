@php
$icons = [
    'villager' => '🏘️',
    'seer' => '👁️',
    'witch' => '🧙',
    'hunter' => '🏹',
    'bodyguard' => '🛡️',
    'little_girl' => '👧',
    'cupid' => '💘',
    'elder' => '👑',
    'scapegoat' => '🐐',
    'village_idiot' => '🤡',
    'two_sisters' => '👭',
    'three_brothers' => '👬',
    'stuttering_judge' => '⚖️',
    'knight_with_rusty_sword' => '⚔️',
    'devoted_servant' => '🤝',
    'bear_tamer' => '🐻',
    'fox' => '🦊',
    'werewolf' => '🐺',
    'big_bad_wolf' => '🐾',
    'accursed_wolf_father' => '🦇',
    'white_werewolf' => '🌕',
    'wolf_hound' => '🐕',
    'pied_piper' => '🎵',
    'angel' => '😇',
];
@endphp
<span class="{{ $class ?? '' }}" {{ $attributes->except('class') }}>
    {{ $icons[$roleKey] ?? '❓' }}
</span>
