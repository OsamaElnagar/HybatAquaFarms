<?php

namespace App\Enums;

enum MovementType: string
{
    case Entry = 'entry';
    case Transfer = 'transfer';
    case Harvest = 'harvest';
    case Mortality = 'mortality';

    public function label(): string
    {
        return match ($this) {
            self::Entry => 'إدخال',
            self::Transfer => 'نقل',
            self::Harvest => 'حصاد',
            self::Mortality => 'نفوق',
        };
    }
}
