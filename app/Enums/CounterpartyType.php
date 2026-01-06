<?php

namespace App\Enums;

use App\Models\Driver;
use App\Models\Employee;
use App\Models\Factory;
use App\Models\Trader;
use BackedEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum CounterpartyType: string implements HasIcon, HasLabel
{
    case EMPLOYEE = Employee::class;
    case TRADER = Trader::class;
    case FACTORY = Factory::class;
    case DRIVER = Driver::class;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EMPLOYEE => 'موظف',
            self::TRADER => 'تاجر - حلقه',
            self::FACTORY => 'مصنع - مفرخ',
            self::DRIVER => 'سائق',
        };
    }

    // Optional: Add an icon for each type
    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::EMPLOYEE => Heroicon::OutlinedUserCircle,
            self::TRADER => Heroicon::OutlinedBuildingStorefront,
            self::FACTORY => Heroicon::OutlinedBuildingOffice2,
            self::DRIVER => Heroicon::OutlinedTruck,
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::EMPLOYEE => Color::Gray,
            self::TRADER => Color::Yellow,
            self::FACTORY => Color::Green,
            self::DRIVER => Color::Red,
        };
    }
}
