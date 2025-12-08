<?php

namespace App\Filament\Widgets;

use App\Models\DailyFeedIssue;
use App\Models\Farm;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class FarmFeedConsumptionStats extends BaseWidget
{
    protected $listeners = ['updateCharts' => '$refresh'];

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $filters = request()->query('filters', []);
        $farmId = $filters['farm_id'] ?? null;
        $dateStart = $filters['date_start'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateEnd = $filters['date_end'] ?? now()->format('Y-m-d');

        $query = DailyFeedIssue::query();

        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        if ($dateStart) {
            $query->whereDate('date', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->whereDate('date', '<=', $dateEnd);
        }

        // Clone query for efficiency
        $quantityQuery = clone $query;
        $costQuery = clone $query;
        $batchesQuery = clone $query;

        $totalQuantity = $quantityQuery->sum('quantity');

        // Calculate Cost: Join with feed_items to get standard_cost
        $totalCost = $costQuery->join('feed_items', 'daily_feed_issues.feed_item_id', '=', 'feed_items.id')
            ->sum(\DB::raw('daily_feed_issues.quantity * feed_items.standard_cost'));

        $activeBatches = $batchesQuery->distinct('batch_id')->count('batch_id');

        // Farm Name for context (optional)
        $farmName = $farmId ? Farm::find($farmId)?->name : 'كل المزارع';

        return [
            Stat::make('إجمالي العلف المستهلك', Number::format($totalQuantity, 2).' كجم')
                ->description("للفترة المحددة - {$farmName}")
                ->descriptionIcon('heroicon-m-scale')
                ->color('primary'),

            Stat::make('تيكلفة العلف التقديرية', Number::currency($totalCost, 'EGP'))
                ->description('بناءً على التكلفة المعيارية')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('عدد الدورات النشطة', $activeBatches)
                ->description('التي تم الصرف لها')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('info'),
        ];
    }
}
