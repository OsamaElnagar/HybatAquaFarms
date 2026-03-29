<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EmployeeStatementStatus: string implements HasColor, HasIcon, HasLabel
{
    case Open = 'open';
    case Closed = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Open => 'مفتوح',
            self::Closed => 'مغلق / مسوَّى',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Open => 'success',
            self::Closed => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Open => 'heroicon-o-lock-open',
            self::Closed => 'heroicon-o-lock-closed',
        };
    }
}
