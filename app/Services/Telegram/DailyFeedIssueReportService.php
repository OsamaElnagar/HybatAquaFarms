<?php

namespace App\Services\Telegram;

use App\Models\DailyFeedIssue;
use Carbon\Carbon;

class DailyFeedIssueReportService
{
    public function generateReport(): string
    {
        // Find the latest 2 distinct dates
        $dates = DailyFeedIssue::select('date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->limit(2)
            ->pluck('date');

        if ($dates->isEmpty()) {
            return '<i>لا توجد بيانات منصرف أعلاف مسجلة.</i>';
        }

        $html = "🐟 <b><u>تقرير منصرف الأعلاف اليومي</u></b> 🐟\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($dates as $date) {
            $formattedDate = Carbon::parse($date)->translatedFormat('l, d F Y');
            $html .= "📅 <b>$formattedDate</b>\n";

            $issues = DailyFeedIssue::with(['farm', 'feedItem'])
                ->whereDate('date', $date)
                ->get();

            if ($issues->isEmpty()) {
                $html .= "<i>لا توجد بيانات لهذا اليوم.</i>\n\n";

                continue;
            }

            // Group by farm then feed item
            $groupedByFarm = $issues->groupBy(function ($issue) {
                return $issue->farm ? $issue->farm->name : 'أخرى';
            });

            foreach ($groupedByFarm as $farmName => $farmIssues) {
                $html .= "🌾 <b>مزرعة: $farmName</b>\n";

                $groupedByItem = $farmIssues->groupBy(function ($issue) {
                    return $issue->feedItem ? $issue->feedItem->name : 'علف غير محدد';
                });

                foreach ($groupedByItem as $itemName => $itemIssues) {
                    $totalQuantity = $itemIssues->sum('quantity');
                    $html .= "  ▪️ $itemName: <code>".number_format((float) $totalQuantity, 3)." كجم</code>\n";
                }
                $html .= "\n";
            }
            $html .= "━━━━━━━━━━━━━━━━━━\n\n";
        }

        return $html;
    }
}
