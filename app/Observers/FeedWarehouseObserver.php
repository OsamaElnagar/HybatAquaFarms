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
        $query = FeedWarehouse::query();
        if ($unit->farm_id) {
            $query->where('farm_id', $unit->farm_id);
        } else {
            $query->whereNull('farm_id');
        }

        $lastUnit = $query->latest('id')->first();

        if ($lastUnit && preg_match('/(\d+)$/', $lastUnit->code, $matches)) {
            $number = (int) $matches[1] + 1;
        } else {
            $number = 1;
        }

        $farmCode = $unit->farm ? $unit->farm->code : 'CWH';

        return $farmCode.'-WH-'.str_pad($number, 3, '0', STR_PAD_LEFT);
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
