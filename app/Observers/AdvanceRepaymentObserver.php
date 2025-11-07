<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Models\AdvanceRepayment;

class AdvanceRepaymentObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(AdvanceRepayment $repayment): void
    {
        $employee = $repayment->employeeAdvance?->employee;

        $this->posting->post('employee.advance.repayment', [
            'amount' => (float) $repayment->amount_paid,
            'farm_id' => $employee?->farm_id,
            'date' => $repayment->payment_date?->toDateString(),
            'source_type' => $repayment->getMorphClass(),
            'source_id' => $repayment->id,
            'description' => 'سداد قسط سُلفة',
        ]);
    }
}
