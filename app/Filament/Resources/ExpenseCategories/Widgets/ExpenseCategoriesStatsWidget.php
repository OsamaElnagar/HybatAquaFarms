<?php

namespace App\Filament\Resources\ExpenseCategories\Widgets;

use App\Models\ExpenseCategory;
use App\Models\PettyCashTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ExpenseCategoriesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $total = ExpenseCategory::count();
        $active = ExpenseCategory::where('is_active', true)->count();
        $thisMonthTx = PettyCashTransaction::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->count();

        return [
            Stat::make('بنود المصروفات', number_format($total))
                ->description($active.' نشط')
                ->descriptionIcon('heroicon-o-list-bullet')
                ->color('primary'),

            Stat::make('عمليات هذا الشهر', number_format($thisMonthTx))
                ->description('حركة المصروفات')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),
        ];
    }
}
