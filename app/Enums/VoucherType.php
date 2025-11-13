<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum VoucherType: string implements HasColor, HasIcon, HasLabel
{
    case Receipt = 'receipt';
    case Payment = 'payment';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Receipt => 'قبض',
            self::Payment => 'صرف',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Receipt => 'success',
            self::Payment => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Receipt => 'heroicon-o-arrow-down-tray',
            self::Payment => 'heroicon-o-arrow-up-tray',
        };
    }
}
