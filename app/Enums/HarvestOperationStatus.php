<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum HarvestOperationStatus: string implements HasColor, HasIcon, HasLabel
{
    case Planned = 'planned';
    case Ongoing = 'ongoing';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Planned => 'مخطط',
            self::Ongoing => 'جاري التنفيذ',
            self::Paused => 'متوقف مؤقتاً',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Planned => 'gray',
            self::Ongoing => 'info',
            self::Paused => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Planned => 'heroicon-o-calendar',
            self::Ongoing => 'heroicon-o-arrow-path',
            self::Paused => 'heroicon-o-pause',
            self::Completed => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
