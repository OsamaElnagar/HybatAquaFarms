<?php

namespace App\Services\Telegram;

use App\Models\SalesOrder;
use Carbon\Carbon;

class SalesReportService
{
    public function generateReport(): string
    {
        $today = SalesOrder::whereDate('date', Carbon::today())->sum('net_amount');
        $thisWeek = SalesOrder::whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('net_amount');
        $thisMonth = SalesOrder::whereMonth('date', Carbon::now()->month)->sum('net_amount');

        $todayOrdersCount = SalesOrder::whereDate('date', Carbon::today())->count();
        $monthOrdersCount = SalesOrder::whereMonth('date', Carbon::now()->month)->count();

        $html = "💰 <b><u>تقرير المبيعات</u></b> 💰\n";
        $html .= "<i>ملخص آخر المعاملات</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= '📅 <b>اليوم:</b> <code>' . number_format((float) $today) . " ج.م</code> <i>({$todayOrdersCount} طلب)</i>\n";
        $html .= '📅 <b>هذا الأسبوع:</b> <code>' . number_format((float) $thisWeek) . " ج.م</code>\n";
        $html .= '📅 <b>هذا الشهر:</b> <code>' . number_format((float) $thisMonth) . " ج.م</code> <i>({$monthOrdersCount} طلب)</i>\n";

        return $html;
    }
}
