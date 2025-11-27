<?php

namespace App\Filament\Resources\Vouchers\Widgets;

use App\Models\Voucher;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class VouchersStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $count = Voucher::count();
        $total = (float) Voucher::sum('amount');
        $thisMonth = (float) Voucher::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        return [
            Stat::make('عدد السندات', number_format($count))
                ->description('إجمالي القيود')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('إجمالي المبالغ', number_format($total).' ج.م')
                ->description('كل السندات')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('info'),

            Stat::make('هذا الشهر', number_format($thisMonth).' ج.م')
                ->description('حسب التاريخ')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
        ];
    }
}
