<?php

namespace App\Observers;

use App\Models\Trader;

class TraderObserver
{
    public function creating(Trader $trader): void
    {
        if (! $trader->code) {
            $trader->code = static::generateCode();
        }
    }

    protected static function generateCode(): string
    {
        $lastTrader = Trader::latest('id')->first();
        $number = $lastTrader ? ((int) substr($lastTrader->code, 4)) + 1 : 1;

        return 'TRD-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
