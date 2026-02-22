<?php

namespace App\Services\Telegram;

use App\Models\Voucher;
use Carbon\Carbon;

class ExpenseReportService
{
    public function generateReport(): string
    {
        $vouchers = Voucher::whereMonth('date', Carbon::now()->month)->get();

        $total = $vouchers->sum('amount');
        $count = $vouchers->count();

        $html = "💸 <b><u>مصروفات السندات</u></b> 💸\n";
        $html .= "<i>التدفقات الخارجة هذا الشهر</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= '💵 <b>إجمالي المصروفات:</b> <code>'.number_format((float) $total, 2)." ج.م</code>\n";
        $html .= "🧾 <b>إجمالي السندات:</b> {$count}\n\n";

        if ($vouchers->isNotEmpty()) {
            $html .= "📋 <b><u>أحدث السندات:</u></b>\n";
            $latest = $vouchers->sortByDesc('date')->take(5);
            foreach ($latest as $v) {
                $date = Carbon::parse($v->date)->format('Y-m-d');
                $amt = number_format((float) $v->amount, 2);
                $desc = \Illuminate\Support\Str::limit($v->description ?? 'بدون وصف', 25);
                $html .= "🔹 <code>{$amt} ج.م</code> | {$date}\n";
                $html .= "     <i>{$desc}</i>\n";
            }
        }

        return $html;
    }
}
