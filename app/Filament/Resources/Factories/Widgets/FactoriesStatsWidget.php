<?php

namespace App\Filament\Resources\Factories\Widgets;

use App\Models\Batch;
use App\Models\BatchPayment;
use App\Models\ClearingEntry;
use App\Models\Factory;
use App\Models\FactoryPayment;
use App\Models\FeedMovement;
use App\Models\Voucher;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FactoriesStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $data = Cache::remember('factories_stats_widget', 600, function () {
            $totalFactories = Factory::count();
            $activeFactories = Factory::where('is_active', true)->count();

            $totalSeedPurchases = (float) Batch::whereNotNull('total_cost')->sum('total_cost');

            $totalFeedPurchases = (float) FeedMovement::whereNotNull('feed_movements.factory_id')
                ->where('movement_type', 'in')
                ->join('feed_items', 'feed_movements.feed_item_id', '=', 'feed_items.id')
                ->sum(DB::raw('feed_movements.quantity * feed_items.standard_cost'));

            $totalPurchases = $totalSeedPurchases + $totalFeedPurchases;

            $totalPaidVouchers = (float) Voucher::where('voucher_type', 'payment')
                ->where('counterparty_type', Factory::class)
                ->sum('amount');

            $totalPaidFactoryPayments = (float) FactoryPayment::sum('amount');
            $totalPaidBatchPayments = (float) BatchPayment::sum('amount');
            $totalSettled = (float) ClearingEntry::whereNotNull('factory_id')->sum('amount');

            $totalPayables = max(
                0,
                $totalPurchases
                - $totalPaidVouchers
                - $totalPaidFactoryPayments
                - $totalPaidBatchPayments
                - $totalSettled
            );

            return [
                'totalFactories' => $totalFactories,
                'activeFactories' => $activeFactories,
                'totalPurchases' => $totalPurchases,
                'totalPayables' => $totalPayables,
            ];
        });

        $totalFactories = $data['totalFactories'];
        $activeFactories = $data['activeFactories'];
        $totalPurchases = $data['totalPurchases'];
        $totalPayables = $data['totalPayables'];

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
