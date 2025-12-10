<?php

namespace App\Filament\Widgets;

use App\Models\Farm;
use App\Models\HarvestBox;
use Filament\Widgets\ChartWidget;

class HarvestByFarmChart extends ChartWidget
{
    protected ?string $heading = 'إجمالي الحصاد لكل مزرعة (طن)';

    protected $listeners = ['updateCharts' => '$refresh'];

    protected function getData(): array
    {
        $filters = request()->query('filters', []);
        $startDate = $filters['date_start'] ?? null;
        $endDate = $filters['date_end'] ?? null;

        $farms = Farm::where('status', 'active')->get();
        $labels = [];
        $data = [];

        foreach ($farms as $farm) {
            $query = HarvestBox::query()->whereHas('harvest', function ($q) use ($farm, $startDate, $endDate, $filters) {
                $q->where('farm_id', $farm->id);

                if ($startDate) {
                    $q->whereDate('harvest_date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('harvest_date', '<=', $endDate);
                }
                if (! empty($filters['batch_id'])) {
                    $q->where('batch_id', $filters['batch_id']);
                }
            });

            $weightInTons = $query->sum('weight') / 1000;

            $labels[] = $farm->name;
            $data[] = round($weightInTons, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'الوزن (طن)',
                    'data' => $data,
                    'backgroundColor' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
