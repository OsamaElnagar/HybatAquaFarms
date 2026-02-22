<?php

namespace App\Services\Telegram;

use App\Models\EmployeeAdvance;

class AdvanceReportService
{
    public function generateReport(): string
    {
        $advances = EmployeeAdvance::with('employee')->get();

        $totalCount = $advances->count();
        $totalAmount = $advances->sum('amount');

        $topAdvances = $advances->sortByDesc('amount')->take(5);

        $html = "💵 <b><u>سلف الموظفين</u></b> 💵\n";
        $html .= "<i>الأرصدة والسلف القائمة</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📊 <b>إجمالي عدد السلف:</b> <code>{$totalCount}</code>\n";
        $html .= '💰 <b>المبلغ الإجمالي:</b> <code>'.number_format((float) $totalAmount, 2)." ج.م</code>\n\n";

        if ($topAdvances->isNotEmpty()) {
            $html .= "📋 <b><u>أعلى السلف:</u></b>\n";
            foreach ($topAdvances as $adv) {
                $name = $adv->employee->name ?? 'موظف غير معروف';
                $amt = number_format((float) $adv->amount, 2);
                $html .= "👤 <b>{$name}</b>\n";
                $html .= "     <code>{$amt} ج.م</code>\n";
            }
        }

        return $html;
    }
}
