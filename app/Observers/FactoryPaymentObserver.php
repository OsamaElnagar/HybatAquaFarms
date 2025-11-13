<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\FactoryPayment;

class FactoryPaymentObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(FactoryPayment $payment): void
    {
        $factory = $payment->factory;

        // Get farm_id from the most recent feed movement for this factory
        // Since a factory can supply multiple farms, we try to get a farm context
        $latestFeedMovement = $factory->feedMovements()
            ->where('movement_type', 'in')
            ->latest('date')
            ->first();

        $farmId = $latestFeedMovement?->toWarehouse?->farm_id ?? null;

        // Post accounting entry: Debit Accounts Payable (2110), Credit Cash/Bank (1110)
        try {
            $this->posting->post('factory.payment', [
                'amount' => (float) $payment->amount,
                'farm_id' => $farmId,
                'date' => $payment->date?->toDateString(),
                'source_type' => $payment->getMorphClass(),
                'source_id' => $payment->id,
                'description' => $payment->description ?? "دفعة لمصنع {$factory->name}",
            ]);
        } catch (\Exception $e) {
            // If posting rule doesn't exist, log but don't fail
            \Log::warning('Failed to post factory payment accounting entry: '.$e->getMessage());
        }
    }

    public function updated(FactoryPayment $payment): void
    {
        // If amount or date changed, we might need to update the journal entry
        // For now, we'll leave it as is since modifying journal entries is complex
    }

    public function deleted(FactoryPayment $payment): void
    {
        // If payment is deleted, we should reverse the journal entry
        // This is complex and would require tracking the journal entry ID
        // For now, we'll leave it as is
    }
}
