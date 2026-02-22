<?php

namespace App\Filament\Resources\PettyCashTransactions\Widgets;

use App\Models\PettyCashTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PettyCashTransactionsStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected ?string $heading = 'ملخص الصندوق';

    // fullwidth
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $thisMonthIn = PettyCashTransaction::where('direction', 'in')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
        $thisMonthOut = PettyCashTransaction::where('direction', 'out')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        return [

            Stat::make('المقبوضات الشهر', number_format($thisMonthIn, 0).' EGP ')
                ->description('حركة واردة')
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->color('success'),

            Stat::make('المصروفات الشهر', number_format($thisMonthOut, 0).' EGP ')
                ->description('حركة منصرفة')
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->color('warning'),
        ];
    }
}
