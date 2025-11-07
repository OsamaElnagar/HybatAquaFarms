<?php

namespace App\Enums;

enum UnitType: string
{
    case Pond = 'pond';
    case Tank = 'tank';
    case Cage = 'cage';

    public function label(): string
    {
        return match ($this) {
            self::Pond => 'حوض',
            self::Tank => 'خزان',
            self::Cage => 'قفص',
        };
    }
}
