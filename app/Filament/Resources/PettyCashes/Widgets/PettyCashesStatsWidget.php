<?php

namespace App\Filament\Resources\PettyCashes\Widgets;

use App\Enums\PettyTransacionType;
use App\Models\PettyCash;
use App\Models\PettyCashTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PettyCashesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return Cache::remember('petty_cashes_stats', 600, function () {
            // $totalPettyCashes = PettyCash::count();
            // $activePettyCashes = PettyCash::where('is_active', true)->count();

            // Total Balance Optimization: avoid looping through PC models
            $sumOpeningBalance = PettyCash::sum('opening_balance');
            $sumIn = PettyCashTransaction::where('direction', PettyTransacionType::IN)->sum('amount');
            $sumOut = PettyCashTransaction::where('direction', PettyTransacionType::OUT)->sum('amount');
            $totalBalance = (float) $sumOpeningBalance + (float) $sumIn - (float) $sumOut;

            // This month's expenses optimization: single query instead of per-model query
            $thisMonthExpenses = PettyCashTransaction::where('direction', PettyTransacionType::OUT)
                ->where('date', '>=', Carbon::now()->startOfMonth())
                ->sum('amount');

            return [

                Stat::make('إجمالي الأرصدة', number_format($totalBalance).' EGP ')
                    ->description('الرصيد الإجمالي لجميع العُهد')
                    ->descriptionIcon('heroicon-o-banknotes')
                    ->color('success'),

                Stat::make('المصروفات هذا الشهر', number_format($thisMonthExpenses).' EGP ')
                    ->description('إجمالي المصروفات من جميع العُهد')
                    ->descriptionIcon('heroicon-o-arrow-trending-down')
                    ->color('warning'),

            ];
        });
    }
}
