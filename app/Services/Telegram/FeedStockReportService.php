<?php

namespace App\Services\Telegram;

use App\Models\FeedStock;

class FeedStockReportService
{
    public function generateReport(): string
    {
        $allStocks = FeedStock::with(['feedItem', 'warehouse'])->get();

        $lowStocks = $allStocks->where('current_balance', '<=', 500)->sortBy('current_balance');

        $html = "⚠️ <b><u>FEED STOCK REPORT</u></b> ⚠️\n";
        $html .= "<i>Inventory status & alerts</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        if ($lowStocks->isEmpty()) {
            $html .= "✅ <i>All feed stocks are at healthy levels (Above 500kg).</i>\n";

            return $html;
        }

        $html .= "🚨 <b><u>CRITICAL INVENTORY</u></b>\n\n";

        foreach ($lowStocks->take(10) as $stock) {
            $itemName = $stock->feedItem->name ?? 'Unknown Feed';
            $warehouseName = $stock->warehouse->name ?? 'Unknown Warehouse';
            $balance = number_format($stock->current_balance ?? 0);

            $icon = $stock->current_balance <= 100 ? '🔴' : '🟠';

            $html .= "{$icon} <b>{$itemName}</b>\n";
            $html .= "     📍 {$warehouseName} | ⚖️ <code>{$balance} kg</code>\n";
        }

        return $html;
    }
}
