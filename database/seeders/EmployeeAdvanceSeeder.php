<?php

namespace Database\Seeders;

use App\Enums\AdvanceStatus;
use App\Models\AdvanceRepayment;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Illuminate\Database\Seeder;

class EmployeeAdvanceSeeder extends Seeder
{
    public function run(): void
    {
        Employee::each(function ($employee) {
            // Create 1-3 advances per employee
            for ($i = 0; $i < rand(1, 3); $i++) {
                $amount = rand(1000, 5000);
                $installments = rand(3, 12);
                $installmentAmount = $amount / $installments;
                $repaidInstallments = rand(0, $installments);

                $advance = EmployeeAdvance::create([
                    'advance_number' => 'ADV-'.str_pad($employee->id * 100 + $i, 6, '0', STR_PAD_LEFT),
                    'employee_id' => $employee->id,
                    'request_date' => now()->subMonths(rand(1, 12)),
                    'amount' => $amount,
                    'reason' => ['ظروف خاصة', 'مصروفات طبية', 'مناسبة عائلية', 'طلب شخصي'][rand(0, 3)],
                    'approval_status' => 'approved',
                    'approved_date' => now()->subMonths(rand(1, 12)),
                    'disbursement_date' => now()->subMonths(rand(1, 12)),
                    'installments_count' => $installments,
                    'installment_amount' => $installmentAmount,
                    'balance_remaining' => $amount - ($repaidInstallments * $installmentAmount),
                    'status' => $repaidInstallments >= $installments ? AdvanceStatus::Completed : AdvanceStatus::Active,
                ]);

                // Create repayments
                for ($j = 0; $j < $repaidInstallments; $j++) {
                    AdvanceRepayment::create([
                        'employee_advance_id' => $advance->id,
                        'payment_date' => $advance->disbursement_date->addMonths($j + 1),
                        'amount_paid' => $installmentAmount,
                        'payment_method' => 'salary_deduction',
                        'balance_remaining' => $amount - (($j + 1) * $installmentAmount),
                    ]);
                }
            }
        });
    }
}
