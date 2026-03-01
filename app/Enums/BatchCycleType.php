<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BatchCycleType: string implements HasColor, HasLabel
{
    case Main = 'main';
    case Nursery = 'nursery';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Main => 'دورة أساسية',
            self::Nursery => 'دورة تحضين / زريعة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Main => 'success',
            self::Nursery => 'warning',
        };
    }
}
