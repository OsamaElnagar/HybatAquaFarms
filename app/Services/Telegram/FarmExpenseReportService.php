<?php

namespace App\Services\Telegram;

use App\Enums\FarmExpenseType;
use App\Models\Farm;
use App\Models\FarmExpense;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FarmExpenseReportService
{
    public function generateSummaryReport(): array
    {
        $now = Carbon::now();

        $monthExpenses = FarmExpense::where('type', FarmExpenseType::Expense)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $monthRevenues = FarmExpense::where('type', FarmExpenseType::Revenue)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $totalExpenses = FarmExpense::where('type', FarmExpenseType::Expense)->sum('amount');
        $totalRevenues = FarmExpense::where('type', FarmExpenseType::Revenue)->sum('amount');

        $farms = Farm::withSum(['farmExpenses as expenses_sum' => fn ($q) => $q->where('type', FarmExpenseType::Expense)], 'amount')
            ->withSum(['farmExpenses as revenues_sum' => fn ($q) => $q->where('type', FarmExpenseType::Revenue)], 'amount')
            ->has('farmExpenses')
            ->get();

        $html = "🏗️ <b><u>مصروفات وإيرادات المزارع</u></b> 🏗️\n\n";

        $html .= '<b>مصروفات الشهر الحالي:</b> <code>'.number_format($monthExpenses)." ج.م</code>\n";
        $html .= '<b>إيرادات الشهر الحالي:</b> <code>'.number_format($monthRevenues)." ج.م</code>\n";
        $html .= '<b><u>صافي الشهر:</u></b> <code>'.number_format($monthRevenues - $monthExpenses)." ج.م</code>\n\n";

        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= '<b>إجمالي المصروفات (كل الفترات):</b> <code>'.number_format($totalExpenses)." ج.م</code>\n";
        $html .= '<b>إجمالي الإيرادات (كل الفترات):</b> <code>'.number_format($totalRevenues)." ج.م</code>\n";
        $html .= '<b><u>الصافي الكلي:</u></b> <code>'.number_format($totalRevenues - $totalExpenses)." ج.م</code>\n\n";

        $html .= "━━━━━━━━━━━━━━━━━━\n\n";
        $html .= '<i>اختر مزرعة لعرض تفاصيل مصروفاتها:</i>';

        return [
            'html' => $html,
            'farms' => $farms,
        ];
    }

    public function generateFarmReport(int $farmId): string
    {
        $farm = Farm::find($farmId);

        if (! $farm) {
            return 'المزرعة غير موجودة.';
        }

        $expenses = FarmExpense::where('farm_id', $farmId)
            ->where('type', FarmExpenseType::Expense)
            ->sum('amount');

        $revenues = FarmExpense::where('farm_id', $farmId)
            ->where('type', FarmExpenseType::Revenue)
            ->sum('amount');

        $html = "🏗️ <b>مزرعة: {$farm->name}</b>\n";
        $html .= 'المصروفات: <code>'.number_format($expenses)." ج.م</code>\n";
        $html .= 'الإيرادات: <code>'.number_format($revenues)." ج.م</code>\n";
        $html .= 'الصافي: <code>'.number_format($revenues - $expenses)." ج.م</code>\n\n";
        $html .= "<b>أحدث المعاملات:</b>\n\n";

        $entries = FarmExpense::with(['expenseCategory'])
            ->where('farm_id', $farmId)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        if ($entries->isEmpty()) {
            $html .= "<i>لا توجد معاملات مسجلة لهذه المزرعة.</i>\n";
        } else {
            foreach ($entries as $entry) {
                $dateStr = Carbon::parse($entry->date)->format('Y-m-d');
                $amount = number_format($entry->amount);
                $sign = $entry->type === FarmExpenseType::Revenue ? '➕ إيراد' : '➖ مصروف';
                $category = $entry->expenseCategory?->name;

                $html .= "<b>[$sign]</b> <code>{$amount} ج.م</code> | {$dateStr}\n";
                if ($category) {
                    $html .= "📂 {$category}\n";
                }
                if ($entry->description) {
                    $html .= '<i>'.Str::limit($entry->description, 50)."</i>\n";
                }
                $html .= "\n";
            }
        }

        return $html;
    }
}
