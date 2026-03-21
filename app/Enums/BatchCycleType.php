<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BatchCycleType: string implements HasColor, HasLabel
{
    case Main = 'main';
    case Nursery = 'nursery';
    case Poultry = 'poultry';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Main => 'دورة أساسية',
            self::Nursery => 'دورة تحضين / زريعة',
            self::Poultry => 'دورة دواجن',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Main => 'success',
            self::Nursery => 'warning',
            self::Poultry => 'info',
        };
    }
}
