<?php

namespace App\Observers;

use App\Domain\Accounting\PostingService;
use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Enums\PaymentMethod;
use App\Enums\SalaryStatus;
use App\Models\EmployeeAdvance;
use App\Models\SalaryRecord;
use Illuminate\Support\Facades\Log;

class SalaryRecordObserver
{
    public function __construct(private PostingService $posting)
    {
    }

    public function created(SalaryRecord $salaryRecord): void
    {
        $status = $salaryRecord->status;
        $currentStatus = $status instanceof SalaryStatus ? $status : SalaryStatus::from($status);

        if ($currentStatus !== SalaryStatus::PAID) {
            return;
        }

        $this->postSalaryPayment($salaryRecord);
        $this->processAdvanceRepayments($salaryRecord);
    }

    public function updated(SalaryRecord $salaryRecord): void
    {
        if (!$salaryRecord->wasChanged('status')) {
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
        $this->processAdvanceRepayments($salaryRecord);
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
            Log::warning('Failed to post salary payment accounting entry: ' . $e->getMessage(), [
                'salary_record_id' => $salaryRecord->id,
            ]);
        }
    }

    protected function processAdvanceRepayments(SalaryRecord $salaryRecord): void
    {
        if ((float) $salaryRecord->advances_deducted <= 0) {
            return;
        }

        // Avoid double-processing if repayments already exist for this salary record
        if ($salaryRecord->advanceRepayments()->exists()) {
            return;
        }

        $amountToDeduct = (float) $salaryRecord->advances_deducted;

        // Get active and approved advances for this employee, ordered by oldest first
        $activeAdvances = EmployeeAdvance::query()
            ->where('employee_id', $salaryRecord->employee_id)
            ->where('status', AdvanceStatus::Active)
            ->where('approval_status', AdvanceApprovalStatus::APPROVED)
            ->orderBy('disbursement_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($activeAdvances as $advance) {
            /** @var \App\Models\EmployeeAdvance $advance */
            if ($amountToDeduct <= 0) {
                break;
            }

            $advanceBalance = (float) $advance->balance_remaining;
            $deductionForThisAdvance = min($advanceBalance, $amountToDeduct);

            if ($deductionForThisAdvance <= 0) {
                continue;
            }

            // Create repayment
            $advance->repayments()->create([
                'payment_date' => $salaryRecord->payment_date ?? now(),
                'amount_paid' => $deductionForThisAdvance,
                'payment_method' => PaymentMethod::SALARY_DEDUCTION,
                'salary_record_id' => $salaryRecord->id,
                'balance_remaining' => max(0, $advanceBalance - $deductionForThisAdvance),
                'notes' => "خصم تلقائي من راتب فترة {$salaryRecord->pay_period_start?->format('Y-m-d')}",
            ]);

            // Update advance balance and status
            $newBalance = max(0, $advanceBalance - $deductionForThisAdvance);
            $newStatus = $newBalance == 0 ? AdvanceStatus::Completed : AdvanceStatus::Active;

            $advance->update([
                'balance_remaining' => $newBalance,
                'status' => $newStatus,
            ]);

            $amountToDeduct -= $deductionForThisAdvance;
        }
    }
}
