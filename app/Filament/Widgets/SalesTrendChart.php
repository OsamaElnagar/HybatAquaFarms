<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Widgets\ChartWidget;

class SalesTrendChart extends ChartWidget
{
    protected ?string $heading = 'تحليل إتجاه المبيعات (صافي)';

    protected int|string|array $columnSpan = 'full';

    protected $listeners = ['updateCharts' => '$refresh'];

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $filters = request()->query('filters', []);
        $startDate = $filters['date_start'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $filters['date_end'] ?? now()->format('Y-m-d');
        $farmId = $filters['farm_id'] ?? null;
        $batchId = $filters['batch_id'] ?? null;

        if ($batchId) {
            // Filter by batch via order items
            // We want Sales Date vs Subtotal of items from this batch
            $query = \App\Models\OrderItem::query()
                ->selectRaw('DATE(sales_orders.date) as day, sum(orders_items.subtotal) as total')
                ->join('orders', 'orders_items.order_id', '=', 'orders.id')
                ->join('order_sales_order', 'orders.id', '=', 'order_sales_order.order_id')
                ->join('sales_orders', 'order_sales_order.sales_order_id', '=', 'sales_orders.id')
                ->join('harvest_operations', 'orders.harvest_operation_id', '=', 'harvest_operations.id')
                ->where('harvest_operations.batch_id', $batchId)
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
