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

        $html = "💵 <b><u>EMPLOYEE ADVANCES</u></b> 💵\n";
        $html .= "<i>Outstanding balances & loans</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📊 <b>Total Advances:</b> <code>{$totalCount}</code>\n";
        $html .= '💰 <b>Total Amount:</b> <code>'.number_format((float) $totalAmount, 2)." EGP</code>\n\n";

        if ($topAdvances->isNotEmpty()) {
            $html .= "📋 <b><u>Top Advances:</u></b>\n";
            foreach ($topAdvances as $adv) {
                $name = $adv->employee->name ?? 'Unknown Employee';
                $amt = number_format((float) $adv->amount, 2);
                $html .= "👤 <b>{$name}</b>\n";
                $html .= "     <code>{$amt} EGP</code>\n";
            }
        }

        return $html;
    }
}
