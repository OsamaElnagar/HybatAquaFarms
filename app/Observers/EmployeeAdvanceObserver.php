<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\EmployeeAdvance;

class EmployeeAdvanceObserver
{
    public function __construct(private PostingService $posting) {}

    public function creating(EmployeeAdvance $advance): void
    {
        if (! $advance->advance_number) {
            $advance->advance_number = static::generateAdvanceNumber();
        }
    }

    public function created(EmployeeAdvance $advance): void
    {
        $this->posting->post('employee.advance', [
            'amount' => (float) $advance->amount,
            'farm_id' => $advance->employee?->farm_id,
            'date' => $advance->disbursement_date?->toDateString() ?? $advance->request_date?->toDateString(),
            'source_type' => $advance->getMorphClass(),
            'source_id' => $advance->id,
            'description' => $advance->reason,
        ]);
    }

    protected static function generateAdvanceNumber(): string
    {
        $lastAdvance = EmployeeAdvance::latest('id')->first();
        $number = $lastAdvance ? ((int) substr($lastAdvance->advance_number, 4)) + 1 : 1;

        return 'ADV-'.str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
