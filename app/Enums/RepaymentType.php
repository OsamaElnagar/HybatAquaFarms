<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RepaymentType: string implements HasColor, HasIcon, HasLabel
{
    case Cash = 'cash';
    case Netting = 'netting';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cash => 'سداد نقدي',
            self::Netting => 'خصم من المبيعات',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Cash => 'success',
            self::Netting => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Cash => 'heroicon-o-banknotes',
            self::Netting => 'heroicon-o-arrows-right-left',
        };
    }
}
