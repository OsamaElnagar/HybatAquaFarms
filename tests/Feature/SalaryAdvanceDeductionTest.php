<?php

use App\Domain\Accounting\PostingService;
use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Enums\PaymentMethod;
use App\Enums\SalaryStatus;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\JournalEntry;
use App\Models\SalaryRecord;

beforeEach(function () {
    $this->mock(PostingService::class, function ($mock) {
        $mock->shouldReceive('post')->andReturn(new JournalEntry);
    });
});

it('automatically creates advance repayments when salary is paid', function () {
    $employee = Employee::factory()->create([
        'basic_salary' => 5000,
    ]);

    $advance = EmployeeAdvance::create([
        'advance_number' => 'ADV-001',
        'employee_id' => $employee->id,
        'request_date' => now(),
        'amount' => 1000,
        'approval_status' => AdvanceApprovalStatus::APPROVED,
        'status' => AdvanceStatus::Active,
        'balance_remaining' => 1000,
    ]);

    $salary = SalaryRecord::create([
        'employee_id' => $employee->id,
        'pay_period_start' => now()->startOfMonth(),
        'pay_period_end' => now()->endOfMonth(),
        'basic_salary' => 5000,
        'advances_deducted' => 500,
        'net_salary' => 4500,
        'status' => SalaryStatus::PAID,
    ]);

    expect($salary->advanceRepayments)->toHaveCount(1);

    $repayment = $salary->advanceRepayments->first();
    expect($repayment->employee_advance_id)->toBe($advance->id)
        ->and((float) $repayment->amount_paid)->toEqual(500.0)
        ->and($repayment->payment_method)->toEqual(PaymentMethod::SALARY_DEDUCTION);

    $advance->refresh();
    expect((float) $advance->balance_remaining)->toEqual(500.0)
        ->and($advance->status)->toEqual(AdvanceStatus::Active);
});

it('marks advance as completed when fully paid off from salary', function () {
    $employee = Employee::factory()->create([
        'basic_salary' => 5000,
    ]);

    $advance = EmployeeAdvance::create([
        'advance_number' => 'ADV-002',
        'employee_id' => $employee->id,
        'request_date' => now(),
        'amount' => 500,
        'approval_status' => AdvanceApprovalStatus::APPROVED,
        'status' => AdvanceStatus::Active,
        'balance_remaining' => 500,
    ]);

    $salary = SalaryRecord::create([
        'employee_id' => $employee->id,
        'pay_period_start' => now()->startOfMonth(),
        'pay_period_end' => now()->endOfMonth(),
        'basic_salary' => 5000,
        'advances_deducted' => 500,
        'net_salary' => 4500,
        'status' => SalaryStatus::PAID,
    ]);

    $advance->refresh();
    expect((float) $advance->balance_remaining)->toEqual(0.0)
        ->and($advance->status)->toEqual(AdvanceStatus::Completed);
});
