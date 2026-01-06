<?php

namespace App\Filament\Widgets;

use App\Models\Farm;
use Filament\Widgets\ChartWidget;

class HarvestByFarmChart extends ChartWidget
{
    protected ?string $heading = 'إجمالي الحصاد لكل مزرعة (طن)';

    protected $listeners = ['updateCharts' => '$refresh'];

    protected ?string $pollingInterval = null;
    protected function getData(): array
    {
        $filters = request()->query('filters', []);
        $startDate = $filters['date_start'] ?? null;
        $endDate = $filters['date_end'] ?? null;

        $farms = Farm::where('status', 'active')->get();
        $labels = [];
        $data = [];

        foreach ($farms as $farm) {
            $query = \App\Models\OrderItem::query();
            $query->whereHas('order.harvestOperation', function ($q) use ($farm, $startDate) {
                $q->where('farm_id', $farm->id);

                // Check date on order (more accurate for stats) or harvest date
                // Using order date for consistency
                /*
                 * Note: If strict harvest date desired:
                 * $q->whereHas('harvest', fn($h) => $h->whereDate('harvest_date'...));
                 * But order date is fine.
                 */

                if ($startDate) {
                    // Check order date
                    // But we are in harvestOperation scope...
                    // Let's rely on joining back to order or harvest
                }
            });

            // Re-structure: filter OrderItems based on Order filters
            $query->whereHas('order', function ($o) use ($farm, $startDate, $endDate, $filters) {
                if ($startDate) {
                    $o->whereDate('date', '>=', $startDate);
                }
                if ($endDate) {
                    $o->whereDate('date', '<=', $endDate);
                }

                $o->whereHas('harvestOperation', function ($hop) use ($farm, $filters) {
                    $hop->where('farm_id', $farm->id);
                    if (! empty($filters['batch_id'])) {
                        $hop->where('batch_id', $filters['batch_id']);
                    }
                });
            });

            $weightInTons = $query->sum('total_weight') / 1000;

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
