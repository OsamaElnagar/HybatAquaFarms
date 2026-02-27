<?php

namespace App\Services\Telegram;

use App\Models\Harvest;
use Carbon\Carbon;

class HarvestReportService
{
    public function generateReport(): string
    {
        $now = Carbon::now();

        $harvests = Harvest::with([
            'harvestOperation.batch.farm',
            'orders.items',
            'orders.salesOrders',
        ])
            ->whereMonth('harvest_date', $now->month)
            ->whereYear('harvest_date', $now->year)
            ->latest('harvest_date')
            ->get();

        $count = $harvests->count();

        $html = "🌾 <b><u>تقرير الحصاد الشهري</u></b> 🌾\n";
        $html .= "📅 <i>شهر {$now->month}/{$now->year}</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        if ($harvests->isEmpty()) {
            $html .= "<i>💤 لم يتم تسجيل أي عمليات حصاد هذا الشهر بعد.</i>\n";

            return $html;
        }

        $totalWeight = 0;
        $totalBoxes = 0;
        $allSalesOrders = collect();

        foreach ($harvests as $harvest) {
            foreach ($harvest->orders as $order) {
                $totalWeight += $order->items->sum('total_weight');
                $totalBoxes += $order->items->sum('quantity');
                $allSalesOrders = $allSalesOrders->merge($order->salesOrders);
            }
        }

        $totalSalesAmount = $allSalesOrders->unique('id')->sum('net_amount');

        $html .= "📊 <b><u>ملخص شهر {$now->month}</u>:</b>\n";
        $html .= "🐟 إجمالي الحصاد: <b>{$count}</b> عملية\n";
        $html .= '📦 إجمالي الصناديق: <b>'.number_format($totalBoxes)." صندوق</b>\n";
        $html .= '⚖️ إجمالي الوزن: <b>'.number_format($totalWeight)." كجم</b>\n";
        $html .= '💰 إجمالي المبيعات التقديرية: <b>'.number_format($totalSalesAmount)." ج.م</b>\n\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";
        $html .= "📋 <b><u>تفاصيل آخر 10 عمليات حصاد</u>:</b>\n\n";

        foreach ($harvests->take(10) as $harvest) {
            $date = $harvest->harvest_date->format('Y-m-d');
            $status = $harvest->status?->getLabel() ?? 'غير معروف';
            $batchCode = $harvest->harvestOperation->batch->batch_code ?? 'غير متوفر';
            $farmName = $harvest->harvestOperation->batch->farm->name ?? 'غير متوفر';
            $harvestNumber = $harvest->harvest_number ?? 'غير متوفر';

            $hWeight = 0;
            $hBoxes = 0;
            $hSalesOrders = collect();

            foreach ($harvest->orders as $order) {
                $hWeight += $order->items->sum('total_weight');
                $hBoxes += $order->items->sum('quantity');
                $hSalesOrders = $hSalesOrders->merge($order->salesOrders);
            }

            $hSalesAmount = $hSalesOrders->unique('id')->sum('net_amount');

            $html .= "🚜 <b>مزرعة:</b> {$farmName} | <b>دورة:</b> <code>{$batchCode}</code>\n\n";
            $html .= "🔖 <b>رقم الحصاد:</b> <code>{$harvestNumber}</code> | 🗓️ <b>التاريخ:</b> {$date} | 🏷️ <b>الحالة:</b> {$status}\n\n";
            $html .= '📦 <b>الصناديق:</b> <code>'.number_format($hBoxes).'</code> | ⚖️ <b>الوزن:</b> <code>'.number_format($hWeight)." كجم</code>\n\n";
            $html .= '💵 <b>مبيعات الحصاد:</b> <code>'.number_format($hSalesAmount)." ج.م</code>\n\n";
            $html .= "──────────────────\n\n";
        }

        if ($count > 10) {
            $html .= "\n\n<i>...و ".($count - 10)." عمليات أخرى في وقت سابق من هذا الشهر.</i>\n\n";
        }

        return $html;
    }
}
