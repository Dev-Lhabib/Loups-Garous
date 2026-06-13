<?php

namespace App\Helpers;

class DecoyHelper
{
    private static ?string $lastType = null;

    public static function random(string $locale = 'en'): array
    {
        $types = ['math', 'riddle', 'count', 'unscramble', 'sequence'];

        if (self::$lastType && in_array(self::$lastType, $types) && count($types) > 1) {
            $types = array_filter($types, fn($t) => $t !== self::$lastType);
        }

        $type = $types[array_rand($types)];
        self::$lastType = $type;

        $translations = $locale === 'fr'
            ? lang("decoys.{$type}", [])
            : lang("decoys.{$type}", []);

        if (empty($translations)) {
            $translations = lang("decoys.{$type}", []);
        }

        $content = $translations[array_rand($translations)];

        return [
            'type' => $type,
            'content' => $content,
        ];
    }

    public static function types(): array
    {
        return ['math', 'riddle', 'count', 'unscramble', 'sequence'];
    }
}