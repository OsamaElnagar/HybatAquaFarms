<?php

namespace App\Enums;

enum SpeciesType: string
{
    case Fish = 'fish';
    case Animal = 'animal';

    public function label(): string
    {
        return match ($this) {
            self::Fish => 'أسماك',
            self::Animal => 'حيوانات',
        };
    }
}
