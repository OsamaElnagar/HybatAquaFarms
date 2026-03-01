<?php

namespace App\Services\Telegram;

use App\Enums\EmployeeStatus;
use App\Models\Employee;

class EmployeeReportService
{
    public function generateSummaryReport(): array
    {
        $activeEmployees = Employee::with('farm')
            ->active()
            ->orderBy('name')
            ->get();

        if ($activeEmployees->isEmpty()) {
            return [
                'html' => 'لا يوجد موظفين نشطين في الوقت الحالي.',
                'employees' => collect(),
            ];
        }

        $totalCount = $activeEmployees->count();
        $totalMonthlySalaries = $activeEmployees->sum('basic_salary');

        $html = "👥 <b><u>قائمة الموظفين النشطين</u></b> 👥\n";
        $html .= "━━━━━━━━━━━━━━━━━━\n\n";

        $html .= "📊 <b>إجمالي عدد الموظفين:</b> <code>{$totalCount}</code>\n";
        $html .= '💰 <b>إجمالي الرواتب الأساسية الشهرية:</b> <code>'.number_format((float) $totalMonthlySalaries)." ج.م</code>\n\n";

        $html .= '<i>اختر الموظف من القائمة أدناه لعرض تفاصيله الشاملة:</i>';

        return [
            'html' => $html,
            'employees' => $activeEmployees,
        ];
    }

    public function generateEmployeeReport(int $employeeId): string
    {
        $employee = Employee::with([
            'farm',
            'managedFarms',
        ])->find($employeeId);

        if (! $employee) {
            return 'الموظف غير موجود.';
        }

        $farmName = $employee->farm->name ?? 'غير محدد';
        $statusIcon = $employee->status === EmployeeStatus::ACTIVE ? '✅' : ($employee->status === EmployeeStatus::INACTIVE ? '⏸️' : '❌');
        $statusText = $employee->status->getLabel() ?? 'غير محدد';

        $html = "👤 <b>الموظف: {$employee->name}</b>\n";
        if ($employee->employee_number) {
            $html .= "🔢 <b>الرقم الوظيفي:</b> <code>{$employee->employee_number}</code>\n";
        }
        $html .= "{$statusIcon} <b>الحالة:</b> {$statusText}\n";
        $html .= "📍 <b>التعيين في مزرعة:</b> {$farmName}\n";
        if ($employee->phone) {
            $html .= "📱 <b>رقم الهاتف:</b> <code>{$employee->phone}</code>\n";
        }

        $hireDate = $employee->hire_date?->format('Y-m-d') ?? 'غير محدد';
        $html .= "📅 <b>تاريخ التعيين:</b> {$hireDate}\n";

        if ($employee->termination_date) {
            $html .= "📅 <b>تاريخ إنهاء الخدمة:</b> {$employee->termination_date->format('Y-m-d')}\n";
        }

        $html .= "━━━━━━━━━━━━━━━━━━\n\n";
        $html .= "💰 <b><u>البيانات المالية</u></b>\n\n";

        $basicSalary = number_format((float) $employee->basic_salary);
        $totalSalariesPaid = number_format((float) $employee->total_salaries_paid);
        $totalOutstandingAdvances = number_format((float) $employee->total_outstanding_advances);
        $salaryRecordsCount = $employee->salary_records_count;
        $advancesCount = $employee->advances_count;

        $html .= "💵 <b>الراتب الأساسي:</b> <code>{$basicSalary} ج.م</code>\n";
        $html .= "💳 <b>إجمالي الرواتب المدفوعة:</b> <code>{$totalSalariesPaid} ج.م</code> (أكثر من {$salaryRecordsCount} سجلات)\n";
        $html .= "⚠️ <b>إجمالي السلف المستحقة:</b> <code>{$totalOutstandingAdvances} ج.م</code> ({$advancesCount} سلفة)\n\n";

        if ($employee->managedFarms->isNotEmpty()) {
            $html .= "━━━━━━━━━━━━━━━━━━\n\n";
            $html .= "🏢 <b><u>المزارع التي يديرها:</u></b>\n\n";
            foreach ($employee->managedFarms as $managedFarm) {
                $html .= "🔹 {$managedFarm->name}\n";
            }
            $html .= "\n";
        }

        if ($employee->notes) {
            $html .= "━━━━━━━━━━━━━━━━━━\n\n";
            $html .= "📝 <b><u>ملاحظات:</u></b>\n";
            $html .= "<i>{$employee->notes}</i>";
        }

        return rtrim($html);
    }
}
