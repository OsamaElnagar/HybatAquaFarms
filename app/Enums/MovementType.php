<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MovementType: string implements HasColor, HasIcon, HasLabel
{
    case Entry = 'entry';
    case Transfer = 'transfer';
    case Harvest = 'harvest';
    case Mortality = 'mortality';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Entry => 'إدخال',
            self::Transfer => 'نقل',
            self::Harvest => 'حصاد',
            self::Mortality => 'نفوق',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Entry => 'success',
            self::Transfer => 'warning',
            self::Harvest => 'gray',
            self::Mortality => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Entry => 'heroicon-o-inbox',
            self::Transfer => 'heroicon-o-arrow-path',
            self::Harvest => 'heroicon-o-wrench',
            self::Mortality => 'heroicon-o-trash',
        };
    }
}
