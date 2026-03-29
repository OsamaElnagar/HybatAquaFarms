<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case CASH = 'cash';
    case BANK = 'bank';
    case CHECK = 'check';
    case SALARY_DEDUCTION = 'salary_deduction';
    case SETTLEMENT = 'settlement';

    public function getColor(): string
    {
        return match ($this) {
            self::CASH => 'success',
            self::BANK => 'info',
            self::CHECK => 'warning',
            self::SALARY_DEDUCTION => 'danger',
            self::SETTLEMENT => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CASH => 'heroicon-o-currency-dollar',
            self::BANK => 'heroicon-o-banknotes',
            self::CHECK => 'heroicon-o-document-text',
            self::SALARY_DEDUCTION => 'heroicon-o-user-minus',
            self::SETTLEMENT => 'heroicon-o-document-duplicate',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::CASH => 'نقدي',
            self::BANK => 'تحويل بنكي',
            self::CHECK => 'شيك',
            self::SALARY_DEDUCTION => 'خصم من المرتب',
            self::SETTLEMENT => 'تسوية بمصاريف',
        };
    }
}
