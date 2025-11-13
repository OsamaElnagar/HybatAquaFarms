<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UnitType: string implements HasColor, HasIcon, HasLabel
{
    case Pond = 'pond';
    case Tank = 'tank';
    case Cage = 'cage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pond => 'حوض',
            self::Tank => 'خزان',
            self::Cage => 'قفص',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pond => 'success',
            self::Tank => 'primary',
            self::Cage => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pond => 'heroicon-o-cube-transparent',
            self::Tank => 'heroicon-o-cube',
            self::Cage => 'heroicon-o-squares-2x2',
        };
    }
}
