<?php

namespace App\Filament\Widgets;

use App\Models\DailyFeedIssue;
use App\Models\Farm;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class FarmFeedConsumptionStats extends BaseWidget
{
    protected $listeners = ['updateCharts' => '$refresh'];

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $filters = request()->query('filters', []);
        $farmId = $filters['farm_id'] ?? null;
        $dateStart = $filters['date_start'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateEnd = $filters['date_end'] ?? now()->format('Y-m-d');

        $cacheKey = "farm_feed_stats_{$farmId}_{$dateStart}_{$dateEnd}";

        return Cache::remember($cacheKey, 600, function () use ($farmId, $dateStart, $dateEnd) {
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

            // Consolidate into a single raw query for efficiency
            $stats = (clone $query)
                ->selectRaw('SUM(quantity) as total_quantity')
                ->selectRaw('COUNT(DISTINCT batch_id) as active_batches')
                ->first();

            // Calculate Cost: Join with feed_items to get standard_cost
            $totalCost = (clone $query)
                ->join('feed_items', 'daily_feed_issues.feed_item_id', '=', 'feed_items.id')
                ->sum(DB::raw('daily_feed_issues.quantity * feed_items.standard_cost'));

            $farmName = $farmId ? Farm::find($farmId)?->name : 'كل المزارع';

            return [
                Stat::make('إجمالي العلف المستهلك', Number::format($stats->total_quantity ?? 0, 2).' كجم')
                    ->description("للفترة المحددة - {$farmName}")
                    ->descriptionIcon('heroicon-m-scale')
                    ->color('primary'),

                Stat::make('تكلفة العلف التقديرية', Number::currency($totalCost, 'EGP'))
                    ->description('بناءً على التكلفة المعيارية')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('success'),

                Stat::make('عدد الدورات النشطة', $stats->active_batches ?? 0)
                    ->description('التي تم الصرف لها')
                    ->descriptionIcon('heroicon-m-beaker')
                    ->color('info'),
            ];
        });
    }
}
