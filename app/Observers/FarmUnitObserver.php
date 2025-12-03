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
        // Get the last unit for the same farm
        $lastUnit = FarmUnit::where('farm_id', $unit->farm_id)
            ->latest('id')
            ->first();

        $number = $lastUnit ? ((int) substr($lastUnit->code, 5)) + 1 : 1;

        return 'UNIT-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
