<?php

namespace App\Filament\Widgets;

use App\Models\DailyFeedIssue;
use Illuminate\Support\Carbon;
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
        $dateStart = Carbon::parse($filters['date_start'] ?? now()->startOfMonth());
        $dateEnd = Carbon::parse($filters['date_end'] ?? now());

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
            return [$item->date->format('Y-m-d') => $item->total_quantity];
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
    }
}
