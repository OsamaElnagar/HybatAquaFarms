<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AccountType: string implements HasColor, HasIcon, HasLabel
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Income = 'income';
    case Expense = 'expense';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Asset => 'أصول',
            self::Liability => 'التزامات',
            self::Equity => 'حقوق ملكية',
            self::Income => 'إيرادات',
            self::Expense => 'مصروفات',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Asset => 'success',
            self::Liability => 'warning',
            self::Equity => 'primary',
            self::Income => 'success',
            self::Expense => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Asset => 'heroicon-o-banknotes',
            self::Liability => 'heroicon-o-exclamation-triangle',
            self::Equity => 'heroicon-o-shield-check',
            self::Income => 'heroicon-o-arrow-down-circle',
            self::Expense => 'heroicon-o-arrow-up-circle',
        };
    }
}
