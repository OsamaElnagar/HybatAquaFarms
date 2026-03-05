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
        $data = Cache::remember('factories_stats_widget_cy', 600, function () {
            $startOfYear = now()->startOfYear();

            $totalSeedPurchases = (float) Batch::whereNotNull('total_cost')
                ->where('entry_date', '>=', $startOfYear)
                ->sum('total_cost');

            $totalFeedPurchases = (float) FeedMovement::whereNotNull('feed_movements.factory_id')
                ->where('movement_type', 'in')
                ->where('date', '>=', $startOfYear)
                ->join('feed_items', 'feed_movements.feed_item_id', '=', 'feed_items.id')
                ->sum(DB::raw('feed_movements.quantity * feed_items.standard_cost'));

            $totalPurchases = $totalSeedPurchases + $totalFeedPurchases;

            $totalPaidVouchers = (float) Voucher::where('voucher_type', 'payment')
                ->where('counterparty_type', Factory::class)
                ->where('date', '>=', $startOfYear)
                ->sum('amount');

            $totalPaidFactoryPayments = (float) FactoryPayment::where('date', '>=', $startOfYear)->sum('amount');
            $totalPaidBatchPayments = (float) BatchPayment::where('date', '>=', $startOfYear)->sum('amount');
            $totalSettled = (float) ClearingEntry::whereNotNull('factory_id')
                ->where('date', '>=', $startOfYear)
                ->sum('amount');

            $totalPayables = max(
                0,
                $totalPurchases
                - $totalPaidVouchers
                - $totalPaidFactoryPayments
                - $totalPaidBatchPayments
                - $totalSettled
            );

            return [
                'totalPurchases' => $totalPurchases,
                'totalPayables' => $totalPayables,
            ];
        });

        $totalPurchases = $data['totalPurchases'];
        $totalPayables = $data['totalPayables'];

        return [
            Stat::make('مشتريات العام الحالي', number_format($totalPurchases) . ' EGP')
                ->description('زريعة وأعلاف (' . now()->year . ')')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('success'),

            Stat::make('مستحقات العام الحالي', number_format($totalPayables) . ' EGP')
                ->description('الرصيد المتبقي عن مشتريات العام الحالي')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($totalPayables > 0 ? 'warning' : 'success'),
        ];
    }
}
