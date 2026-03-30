<?php

namespace App\Filament\Resources\Employees\Widgets;

use App\Models\Employee;
use App\Models\JournalLine;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmployeeStatementStats extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record || ! $this->record instanceof Employee) {
            return [];
        }

        $employee = $this->record;

        // Sum debits for advances (increases employee's debt to us)
        // $totalAdvances = (float) JournalLine::query()
        //     ->whereHas('account', fn (Builder $query) => $query->whereIn('code', ['1150', '5210']))
        //     ->where(function (Builder $q) use ($employee) {
        //         $q->whereHas('journalEntry', fn ($je) => $je->where('employee_id', $employee->id))
        //           ->orWhereHas('journalEntry', fn ($je) => $je->whereHas('employeeStatement', fn ($s) => $s->where('employee_id', $employee->id)));
        //     })
        //     ->sum('debit');

        // // Sum credits for repayments (decreases employee's debt to us)
        // $totalRepayments = (float) JournalLine::query()
        //     ->whereHas('account', fn (Builder $query) => $query->whereIn('code', ['1150', '5210']))
        //     ->where(function (Builder $q) use ($employee) {
        //         $q->whereHas('journalEntry', fn ($je) => $je->where('employee_id', $employee->id))
        //           ->orWhereHas('journalEntry', fn ($je) => $je->whereHas('employeeStatement', fn ($s) => $s->where('employee_id', $employee->id)));
        //     })
        //     ->sum('credit');

        $outstandingBalance = $employee->total_outstanding_advances;

        return [
            // Stat::make('إجمالي السلف', number_format($totalAdvances).' EGP')
            //     ->description('إجمالي كل السلف المسجلة في الحساب')
            //     ->color('primary')
            //     ->icon('heroicon-o-arrow-trending-up'),

            // Stat::make('إجمالي السداد', number_format($totalRepayments).' EGP')
            //     ->description('إجمالي المبالغ المسددة والمصروفات المسواة')
            //     ->color('success')
            //     ->icon('heroicon-o-banknotes'),

            Stat::make('الرصيد المتبقي', number_format($outstandingBalance).' EGP')
                ->description('صافي المديونية القائمة حالياً')
                ->color($outstandingBalance > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-clock'),
        ];
    }
}
