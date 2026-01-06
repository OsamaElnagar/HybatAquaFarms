<?php

namespace App\Filament\Resources\PettyCashes\Widgets;

use App\Models\PettyCash;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PettyCashesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalPettyCashes = PettyCash::count();
        $activePettyCashes = PettyCash::where('is_active', true)->count();

        $totalBalance = PettyCash::get()->sum(fn ($pc) => $pc->current_balance);
        $thisMonthExpenses = PettyCash::query()->get()->sum(function ($pc) {
            return $pc->transactions()
                ->where('direction', 'out')
                ->where('date', '>=', Carbon::now()->startOfMonth())
                ->sum('amount');
        });

        return [
            Stat::make('إجمالي العُهد', number_format($totalPettyCashes))
                ->description($activePettyCashes.' نشط')
                ->descriptionIcon('heroicon-o-wallet')
                ->color('primary'),

            Stat::make('إجمالي الأرصدة', number_format($totalBalance).' EGP ')
                ->description('الرصيد الإجمالي لجميع العُهد')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('المصروفات هذا الشهر', number_format($thisMonthExpenses).' EGP ')
                ->description('إجمالي المصروفات من جميع العُهد')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('warning'),

            Stat::make('متوسط الرصيد', $totalPettyCashes > 0 ? number_format($totalBalance / $totalPettyCashes).' EGP ' : '0.00 EGP')
                ->description('متوسط الرصيد لكل عهدة')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('info'),
        ];
    }
}
