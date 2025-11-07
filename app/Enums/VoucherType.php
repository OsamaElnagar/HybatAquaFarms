<?php

namespace App\Enums;

enum VoucherType: string
{
    case Receipt = 'receipt';
    case Payment = 'payment';

    public function label(): string
    {
        return match ($this) {
            self::Receipt => 'سند قبض',
            self::Payment => 'سند صرف',
        };
    }
}
