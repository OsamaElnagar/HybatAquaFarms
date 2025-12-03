<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PricingUnit: string implements HasColor, HasLabel
{
    case Kilogram = 'kg';
    case Piece = 'piece';
    case Box = 'box';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Kilogram => 'كيلو جرام',
            self::Piece => 'قطعة',
            self::Box => 'صندوق',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Kilogram => 'success',
            self::Piece => 'info',
            self::Box => 'warning',
        };
    }
}
