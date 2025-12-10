<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesTrendChart extends ChartWidget
{
    protected ?string $heading = 'تحليل إتجاه المبيعات (صافي)';
    
    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['updateCharts' => '$refresh'];

    protected function getData(): array
    {
        $filters = request()->query('filters', []);
        $startDate = $filters['date_start'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $filters['date_end'] ?? now()->format('Y-m-d');
        $farmId = $filters['farm_id'] ?? null;
        $batchId = $filters['batch_id'] ?? null;

        if ($batchId) {
            // Filter by batch via harvest boxes
            // We want Sales Date vs Subtotal of boxes from this batch
            $query = \App\Models\HarvestBox::query()
                ->selectRaw('DATE(sales_orders.date) as day, sum(harvest_boxes.subtotal) as total')
                ->join('sales_orders', 'harvest_boxes.sales_order_id', '=', 'sales_orders.id')
                ->where('harvest_boxes.batch_id', $batchId)
                ->whereDate('sales_orders.date', '>=', $startDate)
                ->whereDate('sales_orders.date', '<=', $endDate);
        } else {
            // Standard filters
            $query = SalesOrder::query()
                ->selectRaw('DATE(date) as day, sum(net_amount) as total')
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate);

            if ($farmId) {
                $query->where('farm_id', $farmId);
            }
        }

        $data = $query->groupBy('day')
            ->orderBy('day')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'المبيعات اليومية',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)', // blue-500 with opacity
                    'borderColor' => '#3b82f6', // blue-500
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('day')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
