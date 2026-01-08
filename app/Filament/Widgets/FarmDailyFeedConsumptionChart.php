<?php

namespace App\Filament\Widgets;

use App\Models\DailyFeedIssue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FarmDailyFeedConsumptionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'farmDailyFeedConsumptionChart';

    protected static ?string $heading = 'استهلاك العلف اليومي';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $contentHeight = 300;

    protected $listeners = ['updateCharts' => '$refresh'];

    protected ?string $pollingInterval = null;

    protected function getOptions(): array
    {
        $filters = request()->query('filters', []);
        $farmId = $filters['farm_id'] ?? null;
        $dateStartStr = $filters['date_start'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateEndStr = $filters['date_end'] ?? now()->format('Y-m-d');

        $cacheKey = "farm_daily_consumption_chart_{$farmId}_{$dateStartStr}_{$dateEndStr}";

        return Cache::remember($cacheKey, 600, function () use ($farmId, $dateStartStr, $dateEndStr) {
            $dateStart = Carbon::parse($dateStartStr);
            $dateEnd = Carbon::parse($dateEndStr);

            $query = DailyFeedIssue::query()
                ->whereDate('date', '>=', $dateStart)
                ->whereDate('date', '<=', $dateEnd);

            if ($farmId) {
                $query->where('farm_id', $farmId);
            }

            $data = $query
                ->selectRaw('date, SUM(quantity) as total_quantity')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Map data safely
            $dataByDate = $data->mapWithKeys(function ($item) {
                // Handle both object and string dates
                $dateKey = is_string($item->date) ? Carbon::parse($item->date)->format('Y-m-d') : $item->date->format('Y-m-d');

                return [$dateKey => $item->total_quantity];
            });

            $categories = [];
            $values = [];
            $period = $dateStart->daysUntil($dateEnd);

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $categories[] = $date->format('m/d');
                $values[] = (float) ($dataByDate[$dateStr] ?? 0);
            }

            return [
                'chart' => [
                    'type' => 'area',
                    'height' => 300,
                    'toolbar' => ['show' => false],
                ],
                'series' => [
                    [
                        'name' => 'الاستهلاك (كجم)',
                        'data' => $values,
                    ],
                ],
                'xaxis' => [
                    'categories' => $categories,
                    'labels' => ['style' => ['fontFamily' => 'inherit']],
                ],
                'yaxis' => [
                    'labels' => ['style' => ['fontFamily' => 'inherit']],
                ],
                'colors' => ['#f59e0b'],
                'stroke' => ['curve' => 'smooth'],
                'dataLabels' => ['enabled' => false],
                'tooltip' => [
                    'y' => [
                        'formatter' => "function (val) { return val + ' كجم'; }",
                    ],
                ],
            ];
        });
    }
}
