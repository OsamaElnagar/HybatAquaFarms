<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TreasuryOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $treasuryAccounts = \App\Models\Account::where('is_treasury', true)->get();
        $treasuryAccountIds = $treasuryAccounts->pluck('id');

        $totalBalance = $treasuryAccounts->sum('balance');

        $todayIncoming = \App\Models\JournalLine::whereIn('account_id', $treasuryAccountIds)
            ->whereDate('created_at', today())
            ->sum('debit');

        $todayOutgoing = \App\Models\JournalLine::whereIn('account_id', $treasuryAccountIds)
            ->whereDate('created_at', today())
            ->sum('credit');

        return [
            Stat::make('إجمالي رصيد الخزينة', number_format($totalBalance, 2).' EGP')
                ->description('مجموع أرصدة الصناديق والبنوك')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('وارد اليوم', number_format($todayIncoming, 2).' EGP')
                ->description('إجمالي المقبوضات اليوم')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('صادر اليوم', number_format($todayOutgoing, 2).' EGP')
                ->description('إجمالي المدفوعات اليوم')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
