<?php

namespace App\Services\Telegram;

use App\Models\Batch;

class BatchReportService
{
    public function generateActiveBatchesReport(): string
    {
        $activeBatches = Batch::with(['farm', 'species', 'dailyFeedIssues'])
            ->where('is_cycle_closed', false)
            ->get();

        if ($activeBatches->isEmpty()) {
            return '✅ <i>لا توجد دورات نشطة في الوقت الحالي.</i>';
        }

        $html = "🐟 <b><u>تقرير الدورات النشطة</u></b> 🐟\n";
        $html .= "<i>تم العثور على {$activeBatches->count()} دورات نشطة</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($activeBatches as $batch) {
            $farmName = $batch->farm->name ?? 'مزرعة غير معروفة';
            $speciesName = $batch->species->name ?? 'مختلط/غير معروف';
            $daysActive = $batch->days_since_entry;

            // Mortality logic
            $mortality = $batch->initial_quantity - $batch->current_quantity;
            $mortalityRate = $batch->initial_quantity > 0
                ? round(($mortality / $batch->initial_quantity) * 100, 2)
                : 0;

            // Financials
            $feedConsumed = number_format($batch->total_feed_consumed);
            $totalExpenses = number_format($batch->total_cycle_expenses, 2);
            $totalPaid = number_format($batch->total_paid, 2);
            $balance = number_format($batch->outstanding_balance, 2);

            $html .= "🟦 <b>الدورة:</b> <code>{$batch->batch_code}</code>\n";
            $html .= "📍 <b>المزرعة:</b> {$farmName} | 🐟 {$speciesName}\n";
            $html .= "⏱️ <b>مدة النشاط:</b> {$daysActive} يوم\n";
            $html .= '📉 <b>النافق:</b> '.number_format($mortality)." (<code>{$mortalityRate}%</code>)\n";
            $html .= "🌾 <b>العلف المستهلك:</b> <code>{$feedConsumed} كجم</code>\n";
            $html .= "💸 <b>التكلفة الإجمالية:</b> <code>{$totalExpenses} ج.م</code>\n";
            $html .= "💳 <b>المتبقي دفعه:</b> <code>{$balance} ج.م</code>\n";
            $html .= "\n";
        }

        $html .= '<i>تم إنشاء البيانات في الوقت الفعلي.</i>';

        return $html;
    }
}
