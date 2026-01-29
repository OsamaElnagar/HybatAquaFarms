<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\ExternalCalculationType;
use App\Models\ExternalCalculation;
use Illuminate\Support\Facades\Log;

class ExternalCalculationObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(ExternalCalculation $calc): void
    {
        try {
            $context = [
                'amount' => (float) $calc->amount,
                'farm_id' => $calc->farm_id,
                'date' => $calc->date?->toDateString(),
                'source_type' => $calc->getMorphClass(),
                'source_id' => $calc->id,
                'description' => $calc->description,
            ];

            if ($calc->type === ExternalCalculationType::Payment) {
                $context['debit_account_id'] = $calc->account_id;
                $context['credit_account_id'] = $calc->treasury_account_id;
            } else {
                $context['debit_account_id'] = $calc->treasury_account_id;
                $context['credit_account_id'] = $calc->account_id;
            }

            $entry = $this->posting->post('external.calculation', $context);

            $calc->journal_entry_id = $entry->id;
            $calc->saveQuietly();
        } catch (\Exception $e) {
            Log::error('Failed to post external calculation accounting entry: '.$e->getMessage());
        }
    }
}
