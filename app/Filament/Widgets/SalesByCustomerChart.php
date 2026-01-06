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

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $filters = request()->query('filters', []);
        $startDate = $filters['date_start'] ?? null;
        $endDate = $filters['date_end'] ?? null;
        $farmId = $filters['farm_id'] ?? null;

        $batchId = $filters['batch_id'] ?? null;

        if ($batchId) {
            // Find sales orders that have items belonging to this batch
            $query = SalesOrder::query()
                ->select('sales_orders.trader_id', DB::raw('sum(sales_orders.net_amount) as total'))
                // To filter by batch, we need to join through Orders -> HarvestOperation
                 // This is complex for a simple chart if we want partial split, assuming usually 1 SO = 1 Batch often enough.
                 // Alternatively, sum the total OF THE ITEMS in the sales order that match the batch.
                 // But SalesOrder::net_amount includes taxes/discounts on the whole order.
                 // Let's sum the subtotal of items belonging to the batch as a proxy for "Sales Value" for that batch.
                ->join('order_sales_order', 'sales_orders.id', '=', 'order_sales_order.sales_order_id')
                ->join('orders', 'order_sales_order.order_id', '=', 'orders.id')
                ->join('harvest_operations', 'orders.harvest_operation_id', '=', 'harvest_operations.id');

            // Aggregate carefully: We just want distinct sales orders? No, we want volume.
            // Let's simplify: Sum order items subtotal for this batch and group by trader on the related order.

            // Better approach using OrderItem directly for batch specific view
            $results = \App\Models\OrderItem::query()
                ->select('orders.trader_id', DB::raw('sum(orders_items.subtotal) as total'))
                ->join('orders', 'orders_items.order_id', '=', 'orders.id')
                ->join('harvest_operations', 'orders.harvest_operation_id', '=', 'harvest_operations.id')
                // Ideally we check if it is sold (attached to sales order)
                ->whereHas('order.salesOrders') // Only sold items
                ->where('harvest_operations.batch_id', $batchId)
                ->groupBy('orders.trader_id')
                ->orderByDesc('total')
                ->limit(5)
                ->with('order.trader') // Eager load via order
                ->get();

            // Map results manually since structure changed
            return [
                'datasets' => [
                    [
                        'label' => 'المبيعات',
                        'data' => $results->pluck('total')->toArray(),
                        'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    ],
                ],
                'labels' => $results->map(fn ($item) => \App\Models\Trader::find($item->trader_id)?->name ?? 'Unknown')->toArray(),
            ];

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
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                    ],
                ],
            ],
            'labels' => $results->map(fn ($item) => $item->trader->name ?? 'Unknown')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
