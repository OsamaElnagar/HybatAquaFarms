<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PettyTransacionType: string implements HasColor, HasIcon, HasLabel
{
    case IN = 'in';
    case OUT = 'out';

    public function getColor(): string
    {
        return match ($this) {
            self::IN => 'success',
            self::OUT => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::IN => 'heroicon-o-arrow-down-on-square-stack',
            self::OUT => 'heroicon-o-arrow-up-on-square-stack',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::IN => 'قبض',
            self::OUT => 'صرف',
        };
    }
}
