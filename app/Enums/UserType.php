<?php

namespace App\Enums;

enum UserType: string
{
    case Owner = 'owner';
    case Accountant = 'accountant';
    case FarmManager = 'farm_manager';
    case Worker = 'worker';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'مالك',
            self::Accountant => 'محاسب',
            self::FarmManager => 'مدير مزرعة',
            self::Worker => 'عامل',
        };
    }
}
