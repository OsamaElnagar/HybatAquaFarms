<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = "pending";
    case Partial = "partial";
    case Paid = "paid";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => "معلق",
            self::Partial => "دفع جزئي",
            self::Paid => "مدفوع",
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => "warning",
            self::Partial => "info",
            self::Paid => "success",
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => "heroicon-o-clock",
            self::Partial => "heroicon-o-banknotes",
            self::Paid => "heroicon-o-check-circle",
        };
    }
}
