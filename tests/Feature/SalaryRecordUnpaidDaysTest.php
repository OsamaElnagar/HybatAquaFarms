<?php

use App\Domain\Accounting\PostingService;
use App\Enums\SalaryStatus;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\SalaryRecord;

beforeEach(function () {
    $this->mock(PostingService::class, function ($mock) {
        $mock->shouldReceive('post')->andReturn(new JournalEntry);
    });
});

it('stores unpaid days and days off details on a salary record', function () {
    $employee = Employee::factory()->create(['basic_salary' => 5000]);

    $salary = SalaryRecord::create([
        'employee_id' => $employee->id,
        'pay_period_start' => now()->startOfMonth(),
        'pay_period_end' => now()->endOfMonth(),
        'unpaid_days' => 3,
        'days_off_details' => 'الخميس 5/3 - إجازة مرضية، الجمعة 6/3 - غياب، السبت 7/3 - إجازة',
        'basic_salary' => 4423,
        'net_salary' => 4423,
        'status' => SalaryStatus::PENDING,
    ]);

    $salary->refresh();

    expect($salary->unpaid_days)->toBe(3)
        ->and($salary->days_off_details)->toContain('إجازة مرضية');
});

it('defaults unpaid days to zero when not provided', function () {
    $employee = Employee::factory()->create(['basic_salary' => 5000]);

    $salary = SalaryRecord::create([
        'employee_id' => $employee->id,
        'pay_period_start' => now()->startOfMonth(),
        'pay_period_end' => now()->endOfMonth(),
        'basic_salary' => 5000,
        'net_salary' => 5000,
        'status' => SalaryStatus::PENDING,
    ]);

    $salary->refresh();

    expect($salary->unpaid_days)->toBe(0)
        ->and($salary->days_off_details)->toBeNull();
});

it('creates salary record with days off via factory', function () {
    $salary = SalaryRecord::factory()->create([
        'unpaid_days' => 2,
        'days_off_details' => 'يوم الجمعة، يوم السبت',
    ]);

    expect($salary->unpaid_days)->toBe(2)
        ->and($salary->days_off_details)->toBe('يوم الجمعة، يوم السبت');
});
