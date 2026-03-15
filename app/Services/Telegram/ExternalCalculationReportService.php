<?php

namespace App\Services\Telegram;

use App\Enums\ExternalCalculationType;
use App\Models\ExternalCalculation;
use App\Models\ExternalCalculationEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ExternalCalculationReportService
{
    public function generateSummaryReport(): array
    {
        $receipts = ExternalCalculationEntry::where('type', ExternalCalculationType::Receipt)->sum('amount');
        $payments = ExternalCalculationEntry::where('type', ExternalCalculationType::Payment)->sum('amount');
        $net = $receipts - $payments;

        $monthReceipts = ExternalCalculationEntry::where('type', ExternalCalculationType::Receipt)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        $monthPayments = ExternalCalculationEntry::where('type', ExternalCalculationType::Payment)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->sum('amount');

        $accounts = ExternalCalculation::withSum(['entries as receipts_sum' => fn ($q) => $q->where('type', ExternalCalculationType::Receipt)], 'amount')
            ->withSum(['entries as payments_sum' => fn ($q) => $q->where('type', ExternalCalculationType::Payment)], 'amount')
            ->get();

        $html = "📊 <b><u>تقرير الحسابات الخارجية</u></b> 📊\n\n";

        $html .= '<b>إجمالي المقبوض:</b> <code>'.number_format($receipts)." ج.م</code>\n";
        $html .= '<b>إجمالي المصروف:</b> <code>'.number_format($payments)." ج.م</code>\n";
        $html .= '<b><u>صافي الأثر:</u></b> <code>'.number_format($net)." ج.م</code>\n\n";

        $html .= '<b>مقبوضات الشهر الحالي:</b> <code>'.number_format($monthReceipts)." ج.م</code>\n";
        $html .= '<b>مصروفات الشهر الحالي:</b> <code>'.number_format($monthPayments)." ج.م</code>\n\n";

        $html .= "━━━━━━━━━━━━━━━━━━\n\n";
        $html .= '<i>اختر حساب من القائمة أدناه لعرض حركاته:</i>';

        return [
            'html' => $html,
            'accounts' => $accounts,
        ];
    }

    public function generateAccountReport(int $id): string
    {
        $account = ExternalCalculation::withSum(['entries as receipts_sum' => fn ($q) => $q->where('type', ExternalCalculationType::Receipt)], 'amount')
            ->withSum(['entries as payments_sum' => fn ($q) => $q->where('type', ExternalCalculationType::Payment)], 'amount')
            ->find($id);

        if (! $account) {
            return 'الحساب غير موجود.';
        }

        $balance = ($account->receipts_sum ?? 0) - ($account->payments_sum ?? 0);

        $html = "📊 <b>حساب: {$account->name}</b>\n";
        $html .= 'الرصيد: <code>'.number_format($balance)." ج.م</code>\n\n";
        $html .= "<b>أحدث المعاملات:</b>\n\n";

        $entries = ExternalCalculationEntry::where('external_calculation_id', $id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        if ($entries->isEmpty()) {
            $html .= "<i>لا توجد معاملات مسجلة لهذا الحساب.</i>\n";
        } else {
            foreach ($entries as $entry) {
                $dateStr = Carbon::parse($entry->date)->format('Y-m-d');
                $amount = number_format($entry->amount);

                $sign = $entry->type === ExternalCalculationType::Receipt ? '➕ مقبوض' : '➖ مصروف';

                $html .= "<b>[$sign]</b> <code>{$amount} ج.م</code> | {$dateStr}\n";
                if ($entry->description) {
                    $html .= '<i>'.Str::limit($entry->description, 50)."</i>\n";
                }
                $html .= "\n";
            }
        }

        return $html;
    }
}
