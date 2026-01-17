<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\SalaryStatus;
use App\Models\SalaryRecord;
use Illuminate\Support\Facades\Log;

class SalaryRecordObserver
{
    public function __construct(private PostingService $posting) {}

    public function created(SalaryRecord $salaryRecord): void
    {
        $status = $salaryRecord->status;
        $currentStatus = $status instanceof SalaryStatus ? $status : SalaryStatus::from($status);

        if ($currentStatus !== SalaryStatus::PAID) {
            return;
        }

        $this->postSalaryPayment($salaryRecord);
    }

    public function updated(SalaryRecord $salaryRecord): void
    {
        if (! $salaryRecord->wasChanged('status')) {
            return;
        }

        $original = $salaryRecord->getOriginal('status');
        $current = $salaryRecord->status;

        $originalStatus = $original instanceof SalaryStatus ? $original : SalaryStatus::from($original);
        $currentStatus = $current instanceof SalaryStatus ? $current : SalaryStatus::from($current);

        if ($originalStatus === SalaryStatus::PAID || $currentStatus !== SalaryStatus::PAID) {
            return;
        }

        $this->postSalaryPayment($salaryRecord);
    }

    protected function postSalaryPayment(SalaryRecord $salaryRecord): void
    {
        $salaryRecord->loadMissing('employee');

        try {
            $this->posting->post('salary.payment', [
                'amount' => (float) $salaryRecord->net_salary,
                'date' => $salaryRecord->payment_date?->toDateString() ?? now()->toDateString(),
                'source_type' => $salaryRecord->getMorphClass(),
                'source_id' => $salaryRecord->id,
                'description' => "راتب الموظف {$salaryRecord->employee?->name} - {$salaryRecord->pay_period_start?->format('Y-m')}",
                'farm_id' => $salaryRecord->employee?->farm_id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to post salary payment accounting entry: '.$e->getMessage(), [
                'salary_record_id' => $salaryRecord->id,
            ]);
        }
    }
}
