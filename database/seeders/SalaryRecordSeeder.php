<?php

namespace Database\Seeders;

use App\Enums\EmployeeStatus;
use App\Enums\PaymentMethod;
use App\Enums\SalaryStatus;
use App\Models\Employee;
use App\Models\SalaryRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SalaryRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::query()
            ->where('status', EmployeeStatus::ACTIVE->value)
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        // Seed for ~80% of employees to simulate "most"
        $seeded = $employees->filter(function ($e) {
            return mt_rand(1, 100) <= 80;
        });

        $now = Carbon::now()->startOfMonth();

        foreach ($seeded as $employee) {
            // Seed the past 24 months including current month
            for ($i = 0; $i < 24; $i++) {
                $monthStart = $now->copy()->subMonths($i)->startOfMonth();
                $monthEnd = $now->copy()->subMonths($i)->endOfMonth();

                // Ensure unique per employee per period
                $exists = SalaryRecord::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('pay_period_start', $monthStart)
                    ->whereDate('pay_period_end', $monthEnd)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Compute 26-day prorated basic for full month => exactly 26 days
                $perDay = (float) $employee->basic_salary / 26.0;
                $workingDays = 26.0; // full month

                // Random unpaid days occasionally
                $unpaidDays = (mt_rand(1, 100) <= 15) ? mt_rand(1, 2) : 0;
                $workingDays = max($workingDays - $unpaidDays, 0);

                // Random bonuses/deductions/advances
                $bonuses = (mt_rand(1, 100) <= 30) ? round(mt_rand(50, 400) / 1.0, 2) : 0.0;
                $deductions = (mt_rand(1, 100) <= 25) ? round(mt_rand(50, 300) / 1.0, 2) : 0.0;
                $advancesDeducted = (mt_rand(1, 100) <= 20) ? round(mt_rand(50, 500) / 1.0, 2) : 0.0;

                $basic = round($perDay * $workingDays, 2);
                $net = max(round($basic + $bonuses - $deductions - $advancesDeducted, 2), 0.0);

                SalaryRecord::create([
                    'employee_id' => $employee->id,
                    'pay_period_start' => $monthStart,
                    'pay_period_end' => $monthEnd,
                    'basic_salary' => $basic,
                    'bonuses' => $bonuses,
                    'deductions' => $deductions,
                    'advances_deducted' => $advancesDeducted,
                    'net_salary' => $net,
                    'payment_date' => $monthEnd->copy()->addDays(mt_rand(0, 5)),
                    'payment_method' => collect([PaymentMethod::CASH, PaymentMethod::BANK, PaymentMethod::CHECK])->random()->value,
                    'payment_reference' => null,
                    'status' => collect([SalaryStatus::PENDING, SalaryStatus::PAID])->random()->value,
                    'notes' => null,
                ]);
            }
        }
    }
}
