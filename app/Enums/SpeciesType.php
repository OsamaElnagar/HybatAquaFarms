<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SpeciesType: string implements HasColor, HasIcon, HasLabel
{
    case Fish = 'fish';
    case Animal = 'animal';
    case Poultry = 'poultry';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Fish => 'أسماك',
            self::Animal => 'حيوانات',
            self::Poultry => 'دواجن',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Fish => 'info',
            self::Animal => 'warning',
            self::Poultry => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Fish => 'heroicon-o-sparkles',
            self::Animal => 'heroicon-o-heart',
            self::Poultry => 'heroicon-o-bug-ant', // Placeholder icon for birds
        };
    }
}
