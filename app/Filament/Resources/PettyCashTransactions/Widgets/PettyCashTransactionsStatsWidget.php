<?php

namespace App\Filament\Resources\PettyCashTransactions\Widgets;

use App\Models\PettyCashTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PettyCashTransactionsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $count = PettyCashTransaction::count();
        $thisMonthIn = PettyCashTransaction::where('direction', 'in')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
        $thisMonthOut = PettyCashTransaction::where('direction', 'out')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
        $net = $thisMonthIn - $thisMonthOut;

        return [
            Stat::make('عدد العمليات', number_format($count))
                ->description('إجمالي القيود')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('primary'),

            Stat::make('إيرادات الشهر', number_format($thisMonthIn, 0).' EGP ')
                ->description('حركة واردة')
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->color('success'),

            Stat::make('مصروفات الشهر', number_format($thisMonthOut, 0).' EGP ')
                ->description('حركة منصرفة')
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->color('warning'),

            Stat::make('صافي الشهر', number_format($net, 0).' EGP ')
                ->description('الوارد - المنصرف')
                ->descriptionIcon('heroicon-o-scale')
                ->color($net >= 0 ? 'info' : 'danger'),
        ];
    }
}
