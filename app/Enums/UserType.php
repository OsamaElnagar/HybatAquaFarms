<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserType: string implements HasColor, HasIcon, HasLabel
{
    case Owner = 'owner';
    case Accountant = 'accountant';
    case FarmManager = 'farm_manager';
    case Worker = 'worker';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Owner => 'مالك',
            self::Accountant => 'محاسب',
            self::FarmManager => 'مدير مزرعة',
            self::Worker => 'عامل',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Owner => 'primary',
            self::Accountant => 'info',
            self::FarmManager => 'success',
            self::Worker => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Owner => 'heroicon-o-crown',
            self::Accountant => 'heroicon-o-calculator',
            self::FarmManager => 'heroicon-o-user-circle',
            self::Worker => 'heroicon-o-user',
        };
    }
}
