<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FeedMovementType: string implements HasColor, HasIcon, HasLabel
{
    case In = 'in';
    case Out = 'out';
    case Transfer = 'transfer';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::In => 'وارد',
            self::Out => 'صادر',
            self::Transfer => 'نقل',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::In => 'success',
            self::Out => 'warning',
            self::Transfer => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::In => 'heroicon-o-arrow-down-circle',
            self::Out => 'heroicon-o-arrow-up-circle',
            self::Transfer => 'heroicon-o-arrow-path',
        };
    }
}
