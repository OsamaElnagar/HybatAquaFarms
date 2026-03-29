<?php

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Enums\EmployeeStatementStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\Employees\Pages\StatementOfAccount;
use App\Models\Account;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\PostingRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\be;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    be($this->user);
    $this->employee = Employee::factory()->create();

    // Create accounts and posting rules for accounting entries
    $debitAccount = Account::factory()->create(['code' => '1150', 'name' => 'Advances']);
    $creditAccount = Account::factory()->create(['code' => '1001', 'name' => 'Cash']);

    PostingRule::create([
        'event_key' => 'employee.advance',
        'description' => 'Employee Advance',
        'debit_account_id' => $debitAccount->id,
        'credit_account_id' => $creditAccount->id,
        'is_active' => true,
    ]);
});

it('can render the statement of account page', function () {
    Livewire::test(StatementOfAccount::class, ['record' => $this->employee->id])
        ->assertSuccessful();
});

it('can open a new statement', function () {
    $employee = $this->employee;

    Livewire::test(StatementOfAccount::class, ['record' => $employee->id])
        ->callAction('openNewStatement', [
            'title' => 'Test Statement',
            'notes' => 'Test Notes',
        ])
        ->assertHasNoActionErrors();

    expect($employee->statements()->count())->toBe(1);
    $statement = $employee->statements()->first();
    expect($statement->title)->toBe('Test Statement');
    expect($statement->status)->toBe(EmployeeStatementStatus::Open);
});

it('shows transactions in the statement table', function () {
    $employee = $this->employee;

    // Create an active statement
    $statement = $employee->openNewStatement('Current Month');

    // Create an advance
    $advance = EmployeeAdvance::create([
        'advance_number' => 'ADV-TEST-001',
        'employee_id' => $employee->id,
        'request_date' => now(),
        'amount' => 1000,
        'approval_status' => AdvanceApprovalStatus::APPROVED,
        'status' => AdvanceStatus::Active,
        'balance_remaining' => 1000,
        'disbursement_date' => now(),
    ]);

    // The observer should have created a journal entry linked to the statement
    expect($statement->journalEntries()->count())->toBeGreaterThan(0);

    Livewire::test(StatementOfAccount::class, ['record' => $employee->id])
        ->assertCanSeeTableRecords(
            $statement->journalEntries->flatMap->lines->filter(fn ($line) => in_array($line->account->code, ['1150', '5210']))
        );
});
it('can give an advance to an employee', function () {
    $employee = $this->employee;
    $treasuryAccount = Account::where('code', '1120')->first() ?: Account::factory()->create(['code' => '1120', 'is_treasury' => true]);

    Livewire::test(StatementOfAccount::class, ['record' => $employee->id])
        ->callAction('giveAdvance', [
            'date' => now()->toDateString(),
            'amount' => 500,
            'reason' => 'Test Quick Advance',
            'treasury_account_id' => $treasuryAccount->id,
        ])
        ->assertHasNoActionErrors();

    expect(EmployeeAdvance::where('employee_id', $employee->id)->count())->toBe(1);
    $advance = EmployeeAdvance::where('employee_id', $employee->id)->first();
    expect((float) $advance->amount)->toBe(500.0);
    expect($advance->reason)->toBe('Test Quick Advance');
});

it('can repay an advance from an employee', function () {
    $employee = $this->employee;
    $treasuryAccount = Account::where('code', '1120')->first() ?: Account::factory()->create(['code' => '1120', 'is_treasury' => true]);

    // Create an advance first
    $advance = EmployeeAdvance::create([
        'advance_number' => 'ADV-001',
        'employee_id' => $employee->id,
        'request_date' => now(),
        'amount' => 1000,
        'approval_status' => AdvanceApprovalStatus::APPROVED,
        'status' => AdvanceStatus::Active,
        'balance_remaining' => 1000,
        'disbursement_date' => now(),
    ]);

    // Create posting rule for repayment
    PostingRule::updateOrCreate(
        ['event_key' => 'employee.advance.repayment'],
        [
            'description' => 'Employee Repayment',
            'debit_account_id' => $treasuryAccount->id,
            'credit_account_id' => Account::where('code', '1150')->first()->id,
            'is_active' => true,
        ]
    );

    Livewire::test(StatementOfAccount::class, ['record' => $employee->id])
        ->callAction('repayAdvance', [
            'employee_advance_id' => $advance->id,
            'date' => now()->toDateString(),
            'amount' => 200,
            'payment_method' => PaymentMethod::CASH->value,
            'treasury_account_id' => $treasuryAccount->id,
            'notes' => 'Test Quick Repayment',
        ])
        ->assertHasNoActionErrors();

    expect($advance->repayments()->count())->toBe(1);
    $repayment = $advance->repayments()->first();
    expect((float) $repayment->amount_paid)->toBe(200.0);

    // Refresh advance to check balance
    $advance->refresh();
    expect((float) $advance->balance_remaining)->toBe(800.0);
});

it('cannot repay more than the remaining balance', function () {
    $employee = $this->employee;
    $treasuryAccount = Account::where('code', '1120')->first() ?: Account::factory()->create(['code' => '1120', 'is_treasury' => true]);

    $advance = EmployeeAdvance::create([
        'advance_number' => 'ADV-OVER-001',
        'employee_id' => $employee->id,
        'request_date' => now(),
        'amount' => 500,
        'approval_status' => AdvanceApprovalStatus::APPROVED,
        'status' => AdvanceStatus::Active,
        'balance_remaining' => 500,
        'disbursement_date' => now(),
    ]);

    Livewire::test(StatementOfAccount::class, ['record' => $employee->id])
        ->callAction('repayAdvance', [
            'employee_advance_id' => $advance->id,
            'date' => now()->toDateString(),
            'amount' => 600, // Overpayment
            'payment_method' => PaymentMethod::CASH->value,
            'treasury_account_id' => $treasuryAccount->id,
        ])
        ->assertHasActionErrors(['amount']); // Should fail validation

    expect((float) $advance->refresh()->balance_remaining)->toBe(500.0);
});
