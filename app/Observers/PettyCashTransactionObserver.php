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
        $this->createEmployeeAdvance($pettyCashTransaction);

        // Cache the last used values for this user
        Cache::put('user_' . auth('web')->id() . '_last_petty_cash_date', $pettyCashTransaction->date);
    }
    public function createEmployeeAdvance(PettyCashTransaction $pettyCashTransaction): void
    {
        $pettyCashTransaction->load(['expenseCategory', 'employee']);

        if ($pettyCashTransaction->expenseCategory?->code === 'WORKER_SALARY' && $pettyCashTransaction->employee_id) {
            $advance = EmployeeAdvance::create([
                'employee_id' => $pettyCashTransaction->employee_id,
                'amount' => $pettyCashTransaction->amount,
                'request_date' => $pettyCashTransaction->date,
                'reason' => 'سلفة من عهدة (تلقائي) - معاملة رقم #' . $pettyCashTransaction->id,
                'approval_status' => AdvanceApprovalStatus::APPROVED,
                'approved_by' => $pettyCashTransaction->recorded_by,
                'approved_date' => $pettyCashTransaction->date,
                'disbursement_date' => $pettyCashTransaction->date,
                'status' => AdvanceStatus::Active,
                'notes' => $pettyCashTransaction->description,
                'balance_remaining' => $pettyCashTransaction->amount,
            ]);

            Notification::make()
                ->title('تم إنشاء سلفة تلقائياً')
                ->body("تم تسجيل سلفة للموظف {$pettyCashTransaction->employee->name} بقيمة " . number_format($advance->amount) . ' EGP')
                ->success()
                ->actions([
                    Action::make('view')
                        ->label('عرض / تعديل السلفة')
                        ->url(EmployeeAdvanceResource::getUrl('edit', ['record' => $advance]))
                        ->button(),
                ])
                ->send();
        }
    }
}
