<?php

namespace App\Filament\Widgets;

use App\Models\DailyFeedIssue;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FarmUnitConsumptionChart extends ApexChartWidget
{
    protected static ?string $chartId = 'farmUnitConsumptionChart';

    protected static ?string $heading = 'الاستهلاك حسب الوحدة/الحوض';

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
    }
}
