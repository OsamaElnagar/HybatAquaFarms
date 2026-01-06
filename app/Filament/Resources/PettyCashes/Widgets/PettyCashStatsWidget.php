<?php

namespace App\Filament\Resources\PettyCashes\Widgets;

use App\Models\PettyCash;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PettyCashStatsWidget extends BaseWidget
{
    public ?PettyCash $record = null;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $record = $this->record ?? $this->getParent()->record ?? null;

        if (! $record instanceof PettyCash) {
            return [];
        }

        $currentBalance = $record->current_balance;
        $thisMonth = Carbon::now()->startOfMonth();

        $monthlyExpenses = $record->transactions()
            ->where('direction', 'out')
            ->where('date', '>=', $thisMonth)
            ->sum('amount');

        $monthlyReplenishments = $record->transactions()
            ->where('direction', 'in')
            ->where('date', '>=', $thisMonth)
            ->sum('amount');

        $lastTransaction = $record->transactions()
            ->latest('date')
            ->first();

        return [
            Stat::make('الرصيد الحالي', number_format($currentBalance).' EGP ')
                ->description('الرصيد المتاح حالياً')
                ->descriptionIcon('heroicon-o-wallet')
                ->color($currentBalance > 0 ? 'success' : 'danger'),

            Stat::make('المصروفات هذا الشهر', number_format($monthlyExpenses).' EGP ')
                ->description('إجمالي المصروفات منذ بداية '.Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make('التزويدات هذا الشهر', number_format($monthlyReplenishments).' EGP ')
                ->description('إجمالي التزويدات منذ بداية '.Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make('آخر معاملة', $lastTransaction
                ? $lastTransaction->date->format('Y-m-d')
                : 'لا توجد')
                ->description($lastTransaction
                    ? $lastTransaction->direction->getLabel().' - '.number_format($lastTransaction->amount).' EGP '
                    : '')
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray'),
        ];
    }
}
