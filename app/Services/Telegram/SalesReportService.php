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

        $html = "💰 <b><u>SALES REPORT</u></b> 💰\n";
        $html .= "<i>Summary of latest transactions</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= '📅 <b>Today:</b> <code>'.number_format((float) $today, 2)." EGP</code> <i>({$todayOrdersCount} orders)</i>\n";
        $html .= '📅 <b>This Week:</b> <code>'.number_format((float) $thisWeek, 2)." EGP</code>\n";
        $html .= '📅 <b>This Month:</b> <code>'.number_format((float) $thisMonth, 2)." EGP</code> <i>({$monthOrdersCount} orders)</i>\n";

        return $html;
    }
}
