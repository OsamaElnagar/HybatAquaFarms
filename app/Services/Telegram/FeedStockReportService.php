<?php

namespace App\Services\Telegram;

use App\Models\FeedStock;

class FeedStockReportService
{
    public function generateReport(): string
    {
        $allStocks = FeedStock::with(['feedItem', 'warehouse'])->get();

        $lowStocks = $allStocks->where('current_balance', '<=', 500)->sortBy('current_balance');

        $html = "⚠️ <b><u>تقرير مخزون الأعلاف</u></b> ⚠️\n";
        $html .= "<i>حالة المخزون والتنبيهات</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        if ($lowStocks->isEmpty()) {
            $html .= "✅ <i>جميع مخزونات الأعلاف في مستويات آمنة (أعلى من 500 كجم).</i>\n";

            return $html;
        }

        $html .= "🚨 <b><u>مخزون حرج</u></b>\n\n";

        foreach ($lowStocks->take(10) as $stock) {
            $itemName = $stock->feedItem->name ?? 'علف غير معروف';
            $warehouseName = $stock->warehouse->name ?? 'مستودع غير معروف';
            $balance = number_format($stock->current_balance ?? 0);

            $icon = $stock->current_balance <= 100 ? '🔴' : '🟠';

            $html .= "{$icon} <b>{$itemName}</b>\n";
            $html .= "     📍 {$warehouseName} | ⚖️ <code>{$balance} كجم</code>\n";
        }

        return $html;
    }
}
