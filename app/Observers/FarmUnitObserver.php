<?php

namespace App\Observers;

use App\Models\FarmUnit;

class FarmUnitObserver
{
    public function creating(FarmUnit $unit): void
    {
        if (! $unit->code) {
            $unit->code = static::generateCode($unit);
        }
    }

    protected static function generateCode(FarmUnit $unit): string
    {
        $lastId = FarmUnit::max('id') ?? 0;
        $number = $lastId + 1;

        return 'UNIT-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
