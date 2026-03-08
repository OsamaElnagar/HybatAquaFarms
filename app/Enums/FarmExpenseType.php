<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FarmExpenseType: string implements HasColor, HasIcon, HasLabel
{
    case Expense = 'expense';
    case Revenue = 'revenue';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Expense => 'مصروف',
            self::Revenue => 'إيراد',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Expense => 'danger',
            self::Revenue => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Expense => 'heroicon-o-arrow-up-tray',
            self::Revenue => 'heroicon-o-arrow-down-tray',
        };
    }
}
