<?php

namespace App\Observers;

use App\Models\ExternalCalculation;

class ExternalCalculationObserver
{
    /**
     * Handle the ExternalCalculation "created" event.
     */
    public function created(ExternalCalculation $externalCalculation): void
    {
        $externalCalculation->openNewStatement(
            title: 'دورة أولى - '.$externalCalculation->name,
            notes: 'جلسة افتتاحية تلقائية'
        );
    }

    /**
     * Handle the ExternalCalculation "updated" event.
     */
    public function updated(ExternalCalculation $externalCalculation): void
    {
        //
    }

    /**
     * Handle the ExternalCalculation "deleted" event.
     */
    public function deleted(ExternalCalculation $externalCalculation): void
    {
        //
    }

    /**
     * Handle the ExternalCalculation "restored" event.
     */
    public function restored(ExternalCalculation $externalCalculation): void
    {
        //
    }

    /**
     * Handle the ExternalCalculation "force deleted" event.
     */
    public function forceDeleted(ExternalCalculation $externalCalculation): void
    {
        //
    }
}
