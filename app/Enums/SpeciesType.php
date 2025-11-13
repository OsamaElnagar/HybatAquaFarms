<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SpeciesType: string implements HasColor, HasIcon, HasLabel
{
    case Fish = 'fish';
    case Animal = 'animal';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Fish => 'أسماك',
            self::Animal => 'حيوانات',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Fish => 'info',
            self::Animal => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Fish => 'heroicon-o-sparkles',
            self::Animal => 'heroicon-o-heart',
        };
    }
}
