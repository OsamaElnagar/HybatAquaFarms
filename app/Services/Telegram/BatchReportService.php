<?php

namespace App\Services\Telegram;

use App\Models\Batch;

class BatchReportService
{
    public function generateActiveBatchesReport(): array
    {
        $activeBatches = Batch::with(['farm', 'species', 'dailyFeedIssues'])
            ->where('is_cycle_closed', false)
            ->get();

        if ($activeBatches->isEmpty()) {
            return [
                'html' => 'لا توجد دورات نشطة في الوقت الحالي.',
                'batches' => collect(),
            ];
        }

        // Global Summaries
        $totalLivingFish = $activeBatches->sum('current_quantity');
        $totalFeedConsumed = $activeBatches->sum('total_feed_consumed');
        $totalExpenses = $activeBatches->sum('total_cycle_expenses');

        $html = "<b><u>تقرير الدورات النشطة</u></b>\n\n";

        $html .= "<b><u>إجمالي عام</u>:</b>\n";
        $html .= "▪️ إجمالي الدورات النشطة: <b>{$activeBatches->count()}</b>\n";
        $html .= '▪️ إجمالي الأسماك الحية: <b>'.number_format($totalLivingFish)."</b>\n";
        $html .= '▪️ إجمالي العلف المستهلك: <b>'.number_format($totalFeedConsumed)." كجم</b>\n";
        $html .= '▪️ إجمالي التكاليف المُنْفَقَة: <b>'.number_format($totalExpenses)." ج.م</b>\n\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= '<i>اختر الدورة من القائمة أدناه لعرض تفاصيلها:</i>';

        return [
            'html' => $html,
            'batches' => $activeBatches,
        ];
    }

    public function generateBatchReport(int $batchId): string
    {
        $batch = Batch::with(['farm', 'species', 'dailyFeedIssues'])->find($batchId);

        if (! $batch) {
            return 'الدورة غير موجودة.';
        }

        $farmName = $batch->farm->name ?? 'مزرعة غير معروفة';
        $speciesName = $batch->species->name ?? 'مختلط/غير معروف';
        $daysActive = $batch->days_since_entry;

        // Mortality logic
        $mortality = $batch->initial_quantity - $batch->current_quantity;
        $mortalityRate = $batch->initial_quantity > 0
            ? round(($mortality / $batch->initial_quantity) * 100)
            : 0;

        // Weights
        $initialWeight = number_format($batch->initial_weight_avg);
        $currentWeight = number_format($batch->current_weight_avg);

        // Financials
        $feedConsumed = number_format($batch->total_feed_consumed);
        $totalBatchExpenses = number_format($batch->total_cycle_expenses);
        $balance = number_format($batch->outstanding_balance);

        $html = "<b>مزرعة: {$farmName}</b>\n\n";

        $html .= "<b>الدورة:</b> <code>{$batch->batch_code}</code> ({$speciesName})\n";
        $html .= "مدة النشاط: {$daysActive} يوم\n";
        $html .= 'الكمية: '.number_format($batch->current_quantity).' / '.number_format($batch->initial_quantity)." (نافق: {$mortalityRate}%)\n";

        if ($batch->current_weight_avg > 0) {
            $html .= "متوسط الوزن: {$currentWeight} جرام (دخول: {$initialWeight})\n";
        }

        $html .= "الاستهلاك: <code>{$feedConsumed} كجم</code> علف\n";
        $html .= "التكلفة: <code>{$totalBatchExpenses} ج.م</code>\n";
        $html .= "المتبقي: <code>{$balance} ج.م</code>\n\n";

        $html .= '<i>تم إنشاء البيانات في الوقت الفعلي.</i>';

        return $html;
    }
}
