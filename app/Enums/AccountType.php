<?php

namespace App\Enums;

enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Equity = 'equity';
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Asset => 'أصول',
            self::Liability => 'التزامات',
            self::Equity => 'حقوق ملكية',
            self::Income => 'إيرادات',
            self::Expense => 'مصروفات',
        };
    }
}
