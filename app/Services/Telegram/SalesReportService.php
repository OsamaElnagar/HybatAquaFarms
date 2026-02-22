<?php

namespace App\Services\Telegram;

use App\Models\SalesOrder;
use Carbon\Carbon;

class SalesReportService
{
    public function generateReport(): string
    {
        $today = SalesOrder::whereDate('order_date', Carbon::today())->sum('total_after_discount');
        $thisWeek = SalesOrder::whereBetween('order_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total_after_discount');
        $thisMonth = SalesOrder::whereMonth('order_date', Carbon::now()->month)->sum('total_after_discount');

        $todayOrdersCount = SalesOrder::whereDate('order_date', Carbon::today())->count();
        $monthOrdersCount = SalesOrder::whereMonth('order_date', Carbon::now()->month)->count();

        $html = "💰 <b><u>تقرير المبيعات</u></b> 💰\n";
        $html .= "<i>ملخص آخر المعاملات</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= '📅 <b>اليوم:</b> <code>'.number_format((float) $today, 2)." ج.م</code> <i>({$todayOrdersCount} طلب)</i>\n";
        $html .= '📅 <b>هذا الأسبوع:</b> <code>'.number_format((float) $thisWeek, 2)." ج.م</code>\n";
        $html .= '📅 <b>هذا الشهر:</b> <code>'.number_format((float) $thisMonth, 2)." ج.م</code> <i>({$monthOrdersCount} طلب)</i>\n";

        return $html;
    }
}
