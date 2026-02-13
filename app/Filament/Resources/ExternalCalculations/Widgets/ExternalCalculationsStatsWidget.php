<?php

namespace App\Filament\Resources\ExternalCalculations\Widgets;

use App\Enums\ExternalCalculationType;
use App\Models\ExternalCalculationEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ExternalCalculationsStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $receipts = ExternalCalculationEntry::where('type', ExternalCalculationType::Receipt)->sum('amount');
        $payments = ExternalCalculationEntry::where('type', ExternalCalculationType::Payment)->sum('amount');
        $net = $receipts - $payments;
        $monthReceipts = ExternalCalculationEntry::where('type', ExternalCalculationType::Receipt)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');
        $monthPayments = ExternalCalculationEntry::where('type', ExternalCalculationType::Payment)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        return [
            Stat::make('إجمالي المقبوض', number_format($receipts).' EGP ')
                ->description('من الحسابات الخارجية')
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->color('success'),
            Stat::make('إجمالي المصروف', number_format($payments).' EGP ')
                ->description('من الحسابات الخارجية')
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->color('danger'),
            Stat::make('صافي الأثر', number_format($net).' EGP ')
                ->description('المقبوض - المصروف')
                ->descriptionIcon('heroicon-o-scale')
                ->color($net >= 0 ? 'success' : 'danger'),
            Stat::make('قبض هذا الشهر', number_format($monthReceipts).' EGP ')
                ->description('خلال الشهر الحالي')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success'),
            Stat::make('صرف هذا الشهر', number_format($monthPayments).' EGP ')
                ->description('خلال الشهر الحالي')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('danger'),
        ];
    }
}
