<?php

namespace App\Filament\Resources\ClearingEntries\Widgets;

use App\Models\ClearingEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ClearingEntriesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $count = ClearingEntry::count();
        $total = ClearingEntry::sum('amount');
        $thisMonth = ClearingEntry::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        return [
            Stat::make('عمليات المقاصة', number_format($count))
                ->description('إجمالي القيود')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('primary'),

            Stat::make('إجمالي المبالغ', number_format($total, 2).' ج.م')
                ->description('كل العمليات')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('info'),

            Stat::make('هذا الشهر', number_format($thisMonth, 2).' ج.م')
                ->description('حسب التاريخ')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
        ];
    }
}
