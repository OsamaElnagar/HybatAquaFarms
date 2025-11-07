<?php

namespace App\Enums;

enum BatchStatus: string
{
    case Active = 'active';
    case Harvested = 'harvested';
    case Depleted = 'depleted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'نشط',
            self::Harvested => 'تم الحصاد',
            self::Depleted => 'مستنفد',
        };
    }
}
