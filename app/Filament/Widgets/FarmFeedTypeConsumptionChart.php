<?php

namespace App\Filament\Widgets;

use App\Models\DailyFeedIssue;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FarmFeedTypeConsumptionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'farmFeedTypeConsumptionChart';

    protected static ?string $heading = 'الاستهلاك حسب نوع العلف';

    protected int|string|array $columnSpan = 1;

    protected static ?int $contentHeight = 300;

    protected $listeners = ['updateCharts' => '$refresh'];

    protected function getOptions(): array
    {
        $filters = request()->query('filters', []);
        $farmId = $filters['farm_id'] ?? null;
        $dateStart = Carbon::parse($filters['date_start'] ?? now()->startOfMonth());
        $dateEnd = Carbon::parse($filters['date_end'] ?? now());

        $query = DailyFeedIssue::query()
            ->whereDate('date', '>=', $dateStart)
            ->whereDate('date', '<=', $dateEnd);

        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        $data = $query
            ->selectRaw('feed_item_id, SUM(quantity) as total')
            ->groupBy('feed_item_id')
            ->with('feedItem')
            ->get();

        $labels = $data->map(fn ($item) => $item->feedItem->name ?? 'Unknown')->toArray();
        $series = $data->map(fn ($item) => (float) $item->total)->toArray();

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $series,
            'labels' => $labels,
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '50%',
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => true,
                                'label' => 'الإجمالي',
                                'formatter' => "function (w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(0) + ' كجم' }",
                            ],
                        ],
                    ],
                ],
            ],
            'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
        ];
    }
}
