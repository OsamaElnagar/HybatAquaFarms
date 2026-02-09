<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum FactoryType: string implements HasColor, HasIcon, HasLabel
{
    case FEEDS = 'feeds';
    case SEEDS = 'batches';
    case SUPPLIER = 'supplier';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::FEEDS => 'مصنع اعلاف',
            self::SEEDS => 'مفرخ زريعة',
            self::SUPPLIER => 'مورد',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::FEEDS => Heroicon::FolderOpen,
            self::SEEDS => Heroicon::Ticket,
            self::SUPPLIER => Heroicon::UserGroup,
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FEEDS => 'primary',
            self::SEEDS => 'info',
            self::SUPPLIER => 'success',
        };
    }
}
