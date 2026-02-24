<?php

namespace App\Services\Telegram;

use App\Enums\AdvanceStatus;
use App\Models\Employee;
use App\Models\EmployeeAdvance;

class AdvanceReportService
{
    public function generateSummaryReport(): array
    {
        $activeAdvances = EmployeeAdvance::with('employee')
            ->where('status', AdvanceStatus::Active)
            ->get();

        if ($activeAdvances->isEmpty()) {
            return [
                'html' => "لا توجد سلف نشطة في الوقت الحالي.",
                'employees' => collect(),
            ];
        }

        $totalCount = $activeAdvances->count();
        $totalOriginalAmount = $activeAdvances->sum('amount');
        $totalBalanceRemaining = $activeAdvances->sum('balance_remaining');

        $html = "💵 <b><u>سلف الموظفين</u></b> 💵\n";
        $html .= "<i>الأرصدة والسلف القائمة</i>\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📊 <b>إجمالي عدد السلف:</b> <code>{$totalCount}</code>\n";
        $html .= '💰 <b>إجمالي المبالغ المنصرفة:</b> <code>' . number_format((float) $totalOriginalAmount) . " ج.م</code>\n";
        $html .= '⚠️ <b>إجمالي الرصيد المتبقي:</b> <code>' . number_format((float) $totalBalanceRemaining) . " ج.م</code>\n\n";

        $html .= "<i>اختر الموظف من القائمة أدناه لعرض تفاصيل السلف الخاص به:</i>";

        // Group by employee to avoid duplicate buttons if an employee has multiple active advances
        $employeesWithAdvances = $activeAdvances->map(fn($advance) => $advance->employee)->unique('id')->filter();

        return [
            'html' => $html,
            'employees' => $employeesWithAdvances,
        ];
    }

    public function generateEmployeeAdvanceReport(int $employeeId): string
    {
        $employee = Employee::with([
            'farm',
            'advances' => function ($query) {
                $query->where('status', AdvanceStatus::Active)
                    ->withCount('repayments')
                    ->orderBy('request_date', 'asc');
            }
        ])->find($employeeId);

        if (!$employee) {
            return "الموظف غير موجود.";
        }

        if ($employee->advances->isEmpty()) {
            return "لا توجد سلف نشطة للموظف <b>{$employee->name}</b>.";
        }

        $farmName = $employee->farm->name ?? 'غير محدد';
        $totalOutstanding = $employee->total_outstanding_advances;

        $html = "👤 <b>الموظف: {$employee->name}</b>\n";
        $html .= "📍 <b>المزرعة:</b> {$farmName}\n";
        $html .= '💵 <b>إجمالي السلف المستحقة:</b> <code>' . number_format($totalOutstanding) . " ج.م</code>\n\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📋 <b><u>تفاصيل السلف النشطة:</u></b>\n\n";

        foreach ($employee->advances as $advance) {
            $amount = number_format((float) $advance->amount);
            $balance = number_format((float) $advance->balance_remaining);
            $date = $advance->request_date?->format('Y-m-d') ?? 'غير محدد';
            $repaymentsCount = $advance->repayments_count;

            $html .= "🔹 <b>سلفة رقم:</b> <code>{$advance->advance_number}</code>\n";
            $html .= "   📅 التاريخ: {$date}\n";
            $html .= "   💰 المنصرف: <code>{$amount} ج.م</code>\n";
            $html .= "   ⚠️ المتبقي: <code>{$balance} ج.م</code>\n";

            if ($repaymentsCount > 0) {
                $html .= "   📉 تم الدفع: {$repaymentsCount} مرة\n";
            }

            $html .= "\n";
        }

        return rtrim($html);
    }
}
