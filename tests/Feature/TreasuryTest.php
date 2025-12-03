<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\SalaryStatus;
use App\Models\Account;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PostingRule;
use App\Models\SalaryRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasuryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_it_calculates_treasury_balance()
    {
        // Create Treasury Account manually
        $treasury = Account::create([
            'code' => '1110',
            'name' => 'Main Safe',
            'type' => AccountType::Asset,
            'is_treasury' => true,
            'is_active' => true,
        ]);

        // Add some money (Debit)
        $entry = JournalEntry::create([
            'entry_number' => 'JE-001',
            'date' => now(),
            'is_posted' => true,
            'source_type' => Account::class,
            'source_id' => $treasury->id,
        ]);

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $treasury->id,
            'debit' => 1000,
            'credit' => 0,
        ]);

        // Check balance
        $this->assertEquals(1000, $treasury->fresh()->balance);
    }

    public function test_it_posts_salary_payment_to_treasury()
    {
        // Setup Accounts
        $treasury = Account::create([
            'code' => '1110',
            'name' => 'Main Safe',
            'type' => AccountType::Asset,
            'is_treasury' => true,
            'is_active' => true,
        ]);

        $expenseAccount = Account::create([
            'code' => '5110',
            'name' => 'Salaries Expense',
            'type' => AccountType::Expense,
            'is_active' => true,
        ]);

        // Setup Posting Rule
        PostingRule::create([
            'event_key' => 'salary.payment',
            'description' => 'Salary Payment',
            'debit_account_id' => $expenseAccount->id,
            'credit_account_id' => $treasury->id,
        ]);

        // Create Employee & Salary Record
        $employee = Employee::factory()->create();
        $salary = SalaryRecord::factory()->create([
            'employee_id' => $employee->id,
            'net_salary' => 5000,
            'status' => SalaryStatus::PENDING,
        ]);

        // Pay Salary
        $salary->update(['status' => SalaryStatus::PAID]);

        // Assert Journal Entry Created
        $this->assertDatabaseHas('journal_entries', [
            'source_type' => SalaryRecord::class,
            'source_id' => $salary->id,
        ]);

        // Assert Treasury Balance Decreased (Credit 5000)
        // Asset account: Debit - Credit. 0 - 5000 = -5000.
        $this->assertEquals(-5000, $treasury->fresh()->balance);
    }
}
