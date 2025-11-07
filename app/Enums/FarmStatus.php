<?php

namespace App\Enums;

enum FarmStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Inactive => 'غير نشط',
            self::Maintenance => 'تحت الصيانة',
        };
    }
}
