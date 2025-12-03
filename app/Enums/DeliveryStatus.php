<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DeliveryStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'معلق',
            self::DELIVERED => 'تم التوصيل',
            self::CANCELLED => 'ملغي',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::DELIVERED => 'heroicon-o-truck',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }
}
