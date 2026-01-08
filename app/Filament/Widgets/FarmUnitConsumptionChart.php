<?php

namespace App\Filament\Widgets;

use App\Models\DailyFeedIssue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FarmUnitConsumptionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'farmUnitConsumptionChart';

    protected static ?string $heading = 'الاستهلاك حسب الوحدة/الحوض';

    protected int|string|array $columnSpan = 1;

    protected static ?int $contentHeight = 300;

    protected $listeners = ['updateCharts' => '$refresh'];

    protected ?string $pollingInterval = null;

    protected function getOptions(): array
    {
        $filters = request()->query('filters', []);
        $farmId = $filters['farm_id'] ?? null;
        $dateStartStr = $filters['date_start'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateEndStr = $filters['date_end'] ?? now()->format('Y-m-d');

        $cacheKey = "farm_unit_consumption_chart_{$farmId}_{$dateStartStr}_{$dateEndStr}";

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
                ->selectRaw('unit_id, SUM(quantity) as total')
                ->groupBy('unit_id')
                ->with('unit')
                ->get();

            $labels = $data->map(fn ($item) => $item->unit?->code ?? 'Unit #'.$item->unit_id)->toArray();
            $series = $data->map(fn ($item) => (float) $item->total)->toArray();

            return [
                'chart' => [
                    'type' => 'bar',
                    'height' => 300,
                    'toolbar' => ['show' => false],
                ],
                'plotOptions' => [
                    'bar' => [
                        'borderRadius' => 4,
                        'horizontal' => true,
                        'barHeight' => '50%',
                    ],
                ],
                'dataLabels' => [
                    'enabled' => true,
                    'textAnchor' => 'start',
                    'style' => [
                        'colors' => ['#fff'],
                    ],
                    'formatter' => "function (val) { return val + ' كجم'; }",
                ],
                'series' => [
                    [
                        'name' => 'الاستهلاك',
                        'data' => $series,
                    ],
                ],
                'xaxis' => [
                    'categories' => $labels,
                    'labels' => ['style' => ['fontFamily' => 'inherit']],
                ],
                'yaxis' => [
                    'labels' => ['style' => ['fontFamily' => 'inherit']],
                ],
                'colors' => ['#6366f1'],
            ];
        });
    }
}
