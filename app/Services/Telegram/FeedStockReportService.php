<?php

namespace App\Services\Telegram;

use App\Models\FeedStock;

class FeedStockReportService
{
    public function generateSummaryReport(): array
    {
        $allStocks = FeedStock::with(['feedItem', 'warehouse.farm'])->get();

        $totalWeight = $allStocks->sum('quantity_in_stock');
        $totalWeightTons = $totalWeight / 1000;
        $totalValue = $allStocks->sum('total_value');
        $totalItemsCount = $allStocks->unique('feed_item_id')->count();

        $html = "🌾 <b><u>تقرير مخزون الأعلاف</u></b> 🌾\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📊 <b><u>إجمالي عام</u>:</b>\n";
        $html .= "📦 الأصناف المتوفرة: <b>{$totalItemsCount} صنف</b>\n";
        $html .= '⚖️ إجمالي الوزن: <b>'.number_format($totalWeight).' كجم ('.number_format($totalWeightTons, 2)." طن)</b>\n";
        $html .= '💰 القيمة الإجمالية: <b>'.number_format($totalValue)." ج.م</b>\n\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $lowStocks = $allStocks->where('quantity_in_stock', '<=', 500)->sortBy('quantity_in_stock');

        if ($lowStocks->isNotEmpty()) {
            $html .= "🚨 <b><u>تنبيهات انخفاض المخزون</u></b>\n\n";

            foreach ($lowStocks->take(5) as $stock) {
                $itemName = $stock->feedItem->name ?? 'علف غير معروف';
                $warehouseName = $stock->warehouse->name ?? 'مستودع غير معروف';
                $balance = number_format($stock->quantity_in_stock ?? 0);

                $icon = $stock->quantity_in_stock <= 100 ? '🔴' : '🟠';

                $html .= "{$icon} <b>{$itemName}</b>\n";
                $html .= "     📍 {$warehouseName} | ⚖️ <code>{$balance} كجم</code>\n\n";
            }
        }

        $html .= "👆 <i>اختر مستودعاً من القائمة أدناه لعرض تفاصيله:</i>\n";

        // Get unique warehouses that have stock
        $warehouses = $allStocks->map->warehouse->filter()->unique('id')->values();

        return [
            'html' => $html,
            'warehouses' => $warehouses,
        ];
    }

    public function generateWarehouseReport(int $warehouseId): string
    {
        $stocks = FeedStock::with(['feedItem', 'warehouse.farm'])
            ->where('feed_warehouse_id', $warehouseId)
            ->get();

        if ($stocks->isEmpty()) {
            return '<i>لا توجد أصناف في هذا المستودع.</i>';
        }

        $warehouse = $stocks->first()->warehouse;
        $warehouseName = $warehouse ? $warehouse->name : 'مستودع غير معروف';
        if ($warehouse && $warehouse->farm) {
            $warehouseName .= ' ('.$warehouse->farm->name.')';
        }

        $totalWeight = $stocks->sum('quantity_in_stock');
        $totalItemsCount = $stocks->unique('feed_item_id')->count();

        $html = "🏪 <b><u>مستودع: {$warehouseName}</u></b>\n";
        $html .= "📦 الأصناف: <b>{$totalItemsCount}</b> | ⚖️ الوزن: <b>".number_format($totalWeight)." كجم</b>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($stocks as $stock) {
            $itemName = $stock->feedItem->name ?? 'علف غير معروف';
            $balance = number_format($stock->quantity_in_stock ?? 0);

            $icon = '✅';
            if ($stock->quantity_in_stock <= 100) {
                $icon = '🔴';
            } elseif ($stock->quantity_in_stock <= 500) {
                $icon = '🟠';
            }

            $html .= "{$icon} <b>{$itemName}</b>\n";
            $html .= "    ⚖️ <code>{$balance} كجم</code>\n\n";
        }

        return $html;
    }
}
