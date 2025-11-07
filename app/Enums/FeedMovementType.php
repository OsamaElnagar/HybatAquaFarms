<?php

namespace App\Enums;

enum FeedMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::In => 'وارد',
            self::Out => 'صادر',
            self::Transfer => 'نقل',
        };
    }
}
