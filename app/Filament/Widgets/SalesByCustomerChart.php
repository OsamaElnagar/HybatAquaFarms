<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use App\Models\Trader; // Assuming Trader model exists
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesByCustomerChart extends ChartWidget
{
    protected ?string $heading = 'توزيع المبيعات حسب العميل';

    protected $listeners = ['updateCharts' => '$refresh'];

    protected function getData(): array
    {
        $filters = request()->query('filters', []);
        $startDate = $filters['date_start'] ?? null;
        $endDate = $filters['date_end'] ?? null;
        $farmId = $filters['farm_id'] ?? null;

        $batchId = $filters['batch_id'] ?? null;

        if ($batchId) {
            $query = \App\Models\HarvestBox::query()
                ->select('sales_orders.trader_id', DB::raw('sum(harvest_boxes.subtotal) as total'))
                ->join('sales_orders', 'harvest_boxes.sales_order_id', '=', 'sales_orders.id')
                ->where('harvest_boxes.batch_id', $batchId)
                ->where('harvest_boxes.is_sold', true)
                ->groupBy('sales_orders.trader_id')
                ->orderByDesc('total')
                ->limit(5);

            if ($startDate) {
                $query->whereDate('sales_orders.date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('sales_orders.date', '<=', $endDate);
            }
        } else {
            $query = SalesOrder::query()
                ->select('trader_id', DB::raw('sum(net_amount) as total'))
                ->groupBy('trader_id')
                ->orderByDesc('total')
                ->limit(5); // Top 5 customers

            if ($startDate) {
                $query->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('date', '<=', $endDate);
            }
            if ($farmId) {
                $query->where('farm_id', $farmId);
            }
        }

        $results = $query->with('trader')->get();

        return [
            'datasets' => [
                [
                    'label' => 'المبيعات',
                    'data' => $results->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                    ],
                ],
            ],
            'labels' => $results->map(fn($item) => $item->trader->name ?? 'Unknown')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
