<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BatchSource: string implements HasColor, HasIcon, HasLabel
{
    case Hatchery = 'hatchery';
    case Transfer = 'transfer';
    case Purchase = 'purchase';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Hatchery => 'مفرخة',
            self::Transfer => 'نقل',
            self::Purchase => 'شراء',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Hatchery => 'info',
            self::Transfer => 'warning',
            self::Purchase => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Hatchery => 'heroicon-o-building-office',
            self::Transfer => 'heroicon-o-arrow-right-circle',
            self::Purchase => 'heroicon-o-shopping-cart',
        };
    }
}
