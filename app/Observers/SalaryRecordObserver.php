<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\SalaryStatus;
use App\Models\SalaryRecord;

class SalaryRecordObserver
{
    public function __construct(private PostingService $posting) {}

    public function updated(SalaryRecord $salaryRecord): void
    {
        // Check if status changed to PAID
        if ($salaryRecord->wasChanged('status')) {
            $status = $salaryRecord->status;
            if ($status instanceof SalaryStatus) {
                $isPaid = $status === SalaryStatus::PAID;
            } else {
                $isPaid = $status === SalaryStatus::PAID->value;
            }

            if ($isPaid) {
                $salaryRecord->loadMissing('employee');

                $this->posting->post('salary.payment', [
                    'amount' => (float) $salaryRecord->net_salary,
                    'date' => $salaryRecord->payment_date?->toDateString() ?? now()->toDateString(),
                    'source_type' => $salaryRecord->getMorphClass(),
                    'source_id' => $salaryRecord->id,
                    'description' => "راتب الموظف {$salaryRecord->employee?->name} - {$salaryRecord->pay_period_start?->format('Y-m')}",
                    'farm_id' => $salaryRecord->employee?->farm_id,
                ]);
            }
        }
    }
}
