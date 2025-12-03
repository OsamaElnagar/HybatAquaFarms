<?php

namespace App\Observers;

use App\Models\Farm;

class FarmObserver
{
    public function creating(Farm $farm): void
    {
        if (! $farm->code) {
            $farm->code = static::generateCode();
        }
    }

    protected static function generateCode(): string
    {
        $lastFarm = Farm::latest('id')->first();
        $number = $lastFarm ? ((int) substr($lastFarm->code, 4)) + 1 : 1;

        return 'FRM-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
