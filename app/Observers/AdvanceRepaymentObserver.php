<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\AdvanceStatus;
use App\Enums\PaymentMethod;
use App\Models\AdvanceRepayment;

class AdvanceRepaymentObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(AdvanceRepayment $repayment): void
    {
        $advance = $repayment->employeeAdvance;
        $employee = $advance?->employee;

        // Update the advance balance and status
        if ($advance) {
            $newBalance = max(0, (float) $advance->balance_remaining - (float) $repayment->amount_paid);

            $advance->update([
                'balance_remaining' => $newBalance,
                'status' => $newBalance <= 0 ? AdvanceStatus::Completed : AdvanceStatus::Active,
            ]);
        }

        if ($repayment->payment_method === PaymentMethod::SETTLEMENT) {
            return;
        }

        $this->posting->post('employee.advance.repayment', [
            'amount' => (float) $repayment->amount_paid,
            'farm_id' => $employee?->farm_id,
            'date' => $repayment->payment_date?->toDateString(),
            'source_type' => $repayment->getMorphClass(),
            'source_id' => $repayment->id,
            'description' => $repayment->notes ?: 'سداد قسط سُلفة',
            'employee_statement_id' => $employee?->active_statement?->id,
        ]);
    }
}
