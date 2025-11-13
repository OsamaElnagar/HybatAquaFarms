<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BatchStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Harvested = 'harvested';
    case Depleted = 'depleted';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Harvested => 'تم الحصاد',
            self::Depleted => 'مستنفد',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Harvested => 'warning',
            self::Depleted => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-play-circle',
            self::Harvested => 'heroicon-o-check-badge',
            self::Depleted => 'heroicon-o-stop-circle',
        };
    }
}
