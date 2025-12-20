<?php

namespace App\Filament\Resources\Factories\Widgets;

use App\Models\Factory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FactoriesStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalFactories = Factory::count();
        $activeFactories = Factory::where('is_active', true)->count();

        $totalPurchases = Factory::get()->sum(function ($factory) {
            return $factory->batches()->sum('total_cost') +
                   $factory->feedMovements()->where('movement_type', 'in')->get()->sum(function ($movement) {
                       return $movement->quantity * ($movement->feedItem->standard_cost ?? 0);
                   });
        });

        $totalPayables = Factory::get()->sum(fn ($factory) => $factory->outstanding_balance);

        return [
            Stat::make('إجمالي المصانع/المفرخات', number_format($totalFactories))
                ->description($activeFactories.' نشط')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('إجمالي المشتريات', number_format($totalPurchases).' EGP ')
                ->description('زريعة وأعلاف')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('success'),

            Stat::make('المستحقات للمصانع', number_format($totalPayables).' EGP ')
                ->description('المبالغ المستحقة للدفع')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($totalPayables > 0 ? 'warning' : 'success'),

            Stat::make('متوسط قيمة الطلب', $totalFactories > 0 ? number_format($totalPurchases / $totalFactories).' EGP ' : '0.00 EGP')
                ->description('متوسط المشتريات لكل مصنع')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('info'),
        ];
    }
}
