<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\BatchPayment;

class BatchPaymentObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(BatchPayment $payment): void
    {
        $batch = $payment->batch;
        $factory = $payment->factory;

        // Get farm_id from the batch
        $farmId = $batch?->farm_id ?? null;

        // Post accounting entry: Debit Accounts Payable (2110), Credit Cash/Bank (1110)
        try {
            $this->posting->post('batch.payment', [
                'amount' => (float) $payment->amount,
                'farm_id' => $farmId,
                'date' => $payment->date?->toDateString(),
                'source_type' => $payment->getMorphClass(),
                'source_id' => $payment->id,
                'description' => $payment->description ?? "دفعة لزريعة - دفعة {$batch->batch_code} - {$factory->name}",
            ]);
        } catch (\Exception $e) {
            // If posting rule doesn't exist, log but don't fail
            \Log::warning('Failed to post batch payment accounting entry: '.$e->getMessage());
        }
    }

    public function updated(BatchPayment $payment): void
    {
        // If amount or date changed, we might need to update the journal entry
        // For now, we'll leave it as is since modifying journal entries is complex
    }

    public function deleted(BatchPayment $payment): void
    {
        // If payment is deleted, we should reverse the journal entry
        // This is complex and would require tracking the journal entry ID
        // For now, we'll leave it as is
    }
}
