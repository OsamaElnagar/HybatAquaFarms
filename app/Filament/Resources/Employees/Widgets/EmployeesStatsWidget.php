<?php

namespace App\Filament\Resources\Employees\Widgets;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\SalaryRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EmployeesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $inactiveEmployees = Employee::where('status', 'inactive')->count();

        $totalMonthlyPayroll = Employee::where('status', 'active')->sum('basic_salary');

        $totalAdvances = EmployeeAdvance::sum('amount');
        $outstandingAdvances = EmployeeAdvance::whereIn('status', ['approved', 'partially_paid'])->sum('balance_remaining');

        $thisMonthSalaries = SalaryRecord::whereMonth('pay_period_start', Carbon::now()->month)
            ->whereYear('pay_period_start', Carbon::now()->year)
            ->sum('net_salary');

        return [
            Stat::make('إجمالي الموظفين', number_format($totalEmployees))
                ->description($activeEmployees.' نشط، '.$inactiveEmployees.' غير نشط')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),

            Stat::make('إجمالي الرواتب الشهرية', number_format($totalMonthlyPayroll).' EGP ')
                ->description('رواتب الموظفين النشطين')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('السُلف المستحقة', number_format($outstandingAdvances).' EGP ')
                ->description('من إجمالي '.number_format($totalAdvances).' EGP ')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color($outstandingAdvances > 0 ? 'warning' : 'success'),

            Stat::make('رواتب هذا الشهر', number_format($thisMonthSalaries).' EGP ')
                ->description('إجمالي الرواتب المدفوعة هذا الشهر')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info'),
        ];
    }
}
