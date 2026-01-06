<?php

namespace App\Observers;

use App\Models\FeedWarehouse;

class FeedWarehouseObserver
{
    public function creating(FeedWarehouse $unit): void
    {
        if (! $unit->code) {
            $unit->code = static::generateCode($unit);
        }
    }

    protected static function generateCode(FeedWarehouse $unit): string
    {
        // Get the last unit for the same farm
        $lastUnit = FeedWarehouse::where('farm_id', $unit->farm_id)
            ->latest('id')
            ->first();

        $number = $lastUnit ? ((int) substr($lastUnit->code, 5)) + 1 : 1;

        return 'WAREHOUSE-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Handle the FeedWarehouse "created" event.
     */
    public function created(FeedWarehouse $feedWarehouse): void
    {
        //
    }

    /**
     * Handle the FeedWarehouse "updated" event.
     */
    public function updated(FeedWarehouse $feedWarehouse): void
    {
        //
    }

    /**
     * Handle the FeedWarehouse "deleted" event.
     */
    public function deleted(FeedWarehouse $feedWarehouse): void
    {
        //
    }

    /**
     * Handle the FeedWarehouse "restored" event.
     */
    public function restored(FeedWarehouse $feedWarehouse): void
    {
        //
    }

    /**
     * Handle the FeedWarehouse "force deleted" event.
     */
    public function forceDeleted(FeedWarehouse $feedWarehouse): void
    {
        //
    }
}
