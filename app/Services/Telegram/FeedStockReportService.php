<?php

namespace App\Services\Telegram;

use App\Models\FeedStock;

class FeedStockReportService
{
    public function generateReport(): string
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

        $html .= "🏪 <b><u>المخزون حسب المستودع</u>:</b>\n";

        $stocksByWarehouse = $allStocks->groupBy('feed_warehouse_id');

        foreach ($stocksByWarehouse as $warehouseId => $stocks) {
            $warehouse = $stocks->first()->warehouse;
            $warehouseName = $warehouse ? $warehouse->name : 'مستودع غير معروف';
            if ($warehouse && $warehouse->farm) {
                $warehouseName .= ' ('.$warehouse->farm->name.')';
            }
            $wWeight = $stocks->sum('quantity_in_stock');
            $wItemsCount = $stocks->unique('feed_item_id')->count();

            $html .= " - <b>{$warehouseName}:</b> ".number_format($wWeight).' كجم ('.number_format($wItemsCount)." صنف)\n";
        }
        $html .= "\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $lowStocks = $allStocks->where('quantity_in_stock', '<=', 500)->sortBy('quantity_in_stock');

        if ($lowStocks->isEmpty()) {
            $html .= "✅ <i>جميع مخزونات الأعلاف في مستويات آمنة (أعلى من 500 كجم).</i>\n";

            return $html;
        }

        $html .= "🚨 <b><u>تنبيهات انخفاض المخزون</u></b>\n\n";

        foreach ($lowStocks->take(10) as $stock) {
            $itemName = $stock->feedItem->name ?? 'علف غير معروف';
            $warehouseName = $stock->warehouse->name ?? 'مستودع غير معروف';
            $balance = number_format($stock->quantity_in_stock ?? 0);

            $icon = $stock->quantity_in_stock <= 100 ? '🔴' : '🟠';

            $html .= "{$icon} <b>{$itemName}</b>\n";
            $html .= "     📍 {$warehouseName} | ⚖️ <code>{$balance} كجم</code>\n\n";
        }

        return $html;
    }
}
