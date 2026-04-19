<?php

namespace App\Observers;

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Filament\Resources\EmployeeAdvances\EmployeeAdvanceResource;
use App\Models\EmployeeAdvance;
use App\Models\PettyCashTransaction;
use Cache;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PettyCashTransactionObserver
{
    public function created(PettyCashTransaction $pettyCashTransaction): void
    {
        $this->syncEmployeeAdvance($pettyCashTransaction);

        // Cache the last used values for this user
        Cache::put('user_'.auth('web')->id().'_last_petty_cash_date', $pettyCashTransaction->date);
    }

    public function updated(PettyCashTransaction $pettyCashTransaction): void
    {
        $this->syncEmployeeAdvance($pettyCashTransaction);
    }

    public function deleted(PettyCashTransaction $pettyCashTransaction): void
    {
        $reason = 'سلفة من عهدة (تلقائي) - معاملة رقم #'.$pettyCashTransaction->id;
        EmployeeAdvance::where('reason', $reason)->delete();
    }

    public function syncEmployeeAdvance(PettyCashTransaction $pettyCashTransaction): void
    {
        $pettyCashTransaction->load(['expenseCategory', 'employee']);

        $reason = 'سلفة من عهدة (تلقائي) - معاملة رقم #'.$pettyCashTransaction->id.' - '.$pettyCashTransaction->pettyCash->name;
        $shouldHaveAdvance = $pettyCashTransaction->expenseCategory?->code === 'WORKER_SALARY' && $pettyCashTransaction->employee_id;

        if ($shouldHaveAdvance) {
            $advance = EmployeeAdvance::updateOrCreate(
                ['reason' => $reason],
                [
                    'employee_id' => $pettyCashTransaction->employee_id,
                    'amount' => $pettyCashTransaction->amount,
                    'request_date' => $pettyCashTransaction->date,
                    'approval_status' => AdvanceApprovalStatus::APPROVED,
                    'approved_by' => $pettyCashTransaction->recorded_by,
                    'approved_date' => $pettyCashTransaction->date,
                    'disbursement_date' => $pettyCashTransaction->date,
                    'status' => AdvanceStatus::Active,
                    'notes' => $pettyCashTransaction->description,
                    'balance_remaining' => $pettyCashTransaction->amount,
                ]
            );

            if ($advance->wasRecentlyCreated) {
                Notification::make()
                    ->title('تم إنشاء سلفة تلقائياً')
                    ->body("تم تسجيل سلفة للموظف {$pettyCashTransaction->employee->name} بقيمة ".number_format($advance->amount).' EGP')
                    ->success()
                    ->actions([
                        Action::make('view')
                            ->label('عرض / تعديل السلفة')
                            ->url(EmployeeAdvanceResource::getUrl('edit', ['record' => $advance]))
                            ->button(),
                    ])
                    ->send();
            }
        } else {
            // Clean up if category changed or employee removed
            EmployeeAdvance::where('reason', $reason)->delete();
        }
    }
}
