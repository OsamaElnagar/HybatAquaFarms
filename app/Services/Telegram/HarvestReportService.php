<?php

namespace App\Services\Telegram;

use App\Models\Harvest;
use Carbon\Carbon;

class HarvestReportService
{
    public function generateReport(): string
    {
        $harvests = Harvest::with(['harvestOperation.batch.farm'])
            ->whereMonth('harvest_date', Carbon::now()->month)
            ->latest('harvest_date')
            ->get();

        $count = $harvests->count();

        $html = "🌾 <b><u>تقرير الحصاد</u></b> 🌾\n";
        $html .= "<i>إجمالي عمليات الحصاد هذا الشهر: <b>{$count}</b></i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        if ($harvests->isEmpty()) {
            $html .= "<i>لم يتم تسجيل أي عمليات حصاد هذا الشهر بعد.</i>\n";

            return $html;
        }

        foreach ($harvests->take(5) as $harvest) {
            $date = $harvest->harvest_date->format('Y-m-d');
            $status = $harvest->status->name ?? $harvest->status ?? 'غير معروف';
            $batchCode = $harvest->harvestOperation->batch->batch_code ?? 'غير متوفر';
            $farmName = $harvest->harvestOperation->batch->farm->name ?? 'غير متوفر';
            $yield = number_format($harvest->harvestOperation->actual_yield ?? 0);

            $html .= "📦 <b>الدورة:</b> <code>{$batchCode}</code> ({$farmName})\n";
            $html .= "     🗓️ {$date} | ⚖️ <code>{$yield} كجم</code> | 🏷️ {$status}\n";
        }

        if ($count > 5) {
            $html .= "\n<i>...و ".($count - 5)." عمليات أخرى في وقت سابق من هذا الشهر.</i>\n";
        }

        return $html;
    }
}
