<?php

namespace App\Services\Telegram;

use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CashflowReportService
{
    public function generateReport(): string
    {
        $entries = JournalEntry::whereMonth('date', Carbon::now()->month)->get();

        $count = $entries->count();
        $volume = $entries->sum(function ($entry) {
            return $entry->total_debit ?? 0;
        });

        $html = "🧾 <b><u>التدفقات النقدية والقيود</u></b> 🧾\n";
        $html .= "<i>الحركة المحاسبية هذا الشهر</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📊 <b>إجمالي القيود:</b> <code>{$count}</code> عمليات\n";
        if ($volume > 0) {
            $html .= '🔄 <b>الحجم التقديري:</b> <code>'.number_format((float) $volume, 2)." ج.م</code>\n\n";
        } else {
            $html .= "\n";
        }

        if ($entries->isNotEmpty()) {
            $html .= "📋 <b><u>أحدث القيود:</u></b>\n";
            $latest = $entries->sortByDesc('date')->take(5);
            foreach ($latest as $e) {
                $date = Carbon::parse($e->date)->format('Y-m-d');
                $desc = Str::limit($e->description ?? $e->reference ?? 'قيد يومية', 30);
                $html .= "🔹 {$date} - <i>{$desc}</i>\n";
            }
        }

        return $html;
    }
}
