<?php

namespace Tests\Feature;

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Enums\FarmExpenseType;
use App\Enums\PaymentMethod;
use App\Models\Account;
use App\Models\AdvanceRepayment;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\FarmExpense;
use App\Models\JournalEntry;
use App\Models\PostingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdvanceSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected $category;

    protected $farm;

    protected $employee;

    protected $advance;

    protected $advanceAccount;

    protected $treasuryAccount;

    protected $salaryAccount;

    protected $expenseAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Create accounts
        $this->advanceAccount = Account::create([
            'code' => '1150', 'name' => 'سلف الموظفين', 'type' => 'asset',
        ]);

        $this->treasuryAccount = Account::create([
            'code' => '1110', 'name' => 'Cash', 'type' => 'asset',
        ]);

        $this->salaryAccount = Account::create([
            'code' => '5210', 'name' => 'Salary Expense', 'type' => 'expense',
        ]);

        $this->expenseAccount = Account::create([
            'code' => '5220', 'name' => 'صيانة وإصلاحات', 'type' => 'expense',
        ]);

        // Create Posting Rules
        PostingRule::create([
            'event_key' => 'farm.expense',
            'description' => 'مصروفات مزرعة',
            'is_active' => true,
        ]);

        PostingRule::create([
            'event_key' => 'employee.advance',
            'description' => 'صرف سلفة',
            'debit_account_id' => $this->advanceAccount->id,
            'credit_account_id' => $this->treasuryAccount->id,
            'is_active' => true,
        ]);

        PostingRule::create([
            'event_key' => 'employee.advance.repayment',
            'description' => 'سداد سلفة',
            'debit_account_id' => $this->salaryAccount->id,
            'credit_account_id' => $this->advanceAccount->id,
            'is_active' => true,
        ]);

        // Create an Expense Category
        $this->category = ExpenseCategory::create([
            'name' => 'إصلاحات',
            'is_active' => true,
        ]);

        $this->farm = Farm::factory()->create();
        $this->employee = Employee::factory()->create(['farm_id' => $this->farm->id]);

        $this->advance = EmployeeAdvance::factory()->create([
            'employee_id' => $this->employee->id,
            'advance_number' => 'ADV-TEST-'.rand(100, 999),
            'amount' => 1000,
            'balance_remaining' => 1000,
            'request_date' => now()->toDateString(),
            'status' => AdvanceStatus::Active,
            'approval_status' => AdvanceApprovalStatus::APPROVED,
        ]);
    }

    public function test_can_settle_an_advance_with_farm_expenses()
    {
        $expenses = [
            [
                'farm_id' => $this->farm->id,
                'expense_category_id' => $this->category->id,
                'amount' => 400,
                'description' => 'تصليح مضخة',
            ],
            [
                'farm_id' => $this->farm->id,
                'expense_category_id' => $this->category->id,
                'amount' => 200,
                'description' => 'زيت موتور',
            ],
        ];

        $totalAmount = 600;

        DB::transaction(function () use ($expenses, $totalAmount) {
            $repayment = $this->advance->repayments()->create([
                'payment_date' => now(),
                'amount_paid' => $totalAmount,
                'payment_method' => PaymentMethod::SETTLEMENT,
                'balance_remaining' => $this->advance->balance_remaining - $totalAmount,
                'notes' => 'تسوية بمصاريف',
            ]);

            foreach ($expenses as $expenseData) {
                FarmExpense::create([
                    'farm_id' => $expenseData['farm_id'],
                    'expense_category_id' => $expenseData['expense_category_id'],
                    'amount' => $expenseData['amount'],
                    'date' => now(),
                    'type' => FarmExpenseType::Expense,
                    'description' => $expenseData['description'],
                    'advance_repayment_id' => $repayment->id,
                    'treasury_account_id' => $this->advanceAccount->id,
                    'account_id' => $this->expenseAccount->id,
                ]);
            }
        });

        $this->advance->refresh();

        $this->assertEquals(400.0, (float) $this->advance->balance_remaining);
        $this->assertEquals(1, AdvanceRepayment::count());
        $this->assertEquals(2, FarmExpense::count());

        $repayment = AdvanceRepayment::first();
        $this->assertEquals(PaymentMethod::SETTLEMENT, $repayment->payment_method);

        // Verify Accounting Entries
        $expenseRecords = FarmExpense::all();
        foreach ($expenseRecords as $exp) {
            $this->assertNotNull($exp->journal_entry_id);
            $lines = $exp->journalEntry->lines;

            // Debit: Expense, Credit: Advance Account
            $this->assertEquals((float) $exp->amount, (float) $lines->where('account_id', $this->expenseAccount->id)->first()->debit);
            $this->assertEquals((float) $exp->amount, (float) $lines->where('account_id', $this->advanceAccount->id)->first()->credit);
        }

        // Verify settlement repayment now posts a journal entry crediting 1150 (the advance)
        $this->assertNotNull($repayment->employeeAdvance);
        $repaymentJournalEntry = JournalEntry::where('source_type', 'App\\Models\\AdvanceRepayment')
            ->where('source_id', $repayment->id)
            ->first();
        $this->assertNotNull($repaymentJournalEntry, 'Settlement repayment should create a journal entry');
        $this->assertEquals((float) $totalAmount, (float) $repaymentJournalEntry->lines->sum('debit'));
        $this->assertEquals((float) $totalAmount, (float) $repaymentJournalEntry->lines->sum('credit'));
        $this->assertTrue(
            $repaymentJournalEntry->lines->contains(fn ($line) => (int) $line->account_id === $this->advanceAccount->id && (float) $line->credit > 0),
            'Settlement repayment journal entry should credit the advance account (1150)'
        );
    }
}
