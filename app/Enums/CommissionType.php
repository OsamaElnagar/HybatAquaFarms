<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CommissionType: string implements HasLabel, HasColor
{
    case Percentage = 'percentage';
    case FixedPerKg = 'fixed_per_kg';
    case None = 'none';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Percentage => 'نسبة مئوية',
            self::FixedPerKg => 'مبلغ ثابت للكيلو',
            self::None => 'بدون عمولة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Percentage => 'warning',
            self::FixedPerKg => 'info',
            self::None => 'gray',
        };
    }
}
