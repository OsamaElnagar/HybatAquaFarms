<?php

declare(strict_types=1);

use App\Enums\AccountType;
use App\Enums\FarmExpenseType;
use App\Models\Account;
use App\Models\Farm;
use App\Models\FarmExpense;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PostingRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    PostingRule::create([
        'event_key' => 'farm.expense',
        'description' => 'مصروفات/إيرادات المزرعة',
        'debit_account_id' => null,
        'credit_account_id' => null,
        'is_active' => true,
    ]);
});

function createFarmExpenseTreasuryAccount(): Account
{
    return Account::factory()->create([
        'code' => '1110-FE',
        'name' => 'النقدية بالصندوق',
        'type' => AccountType::Asset,
        'is_treasury' => true,
        'is_active' => true,
    ]);
}

function createFarmExpenseAccount(): Account
{
    return Account::factory()->create([
        'code' => '5280-FE',
        'name' => 'مصروفات متنوعة',
        'type' => AccountType::Expense,
        'is_active' => true,
    ]);
}

function createFarmIncomeAccount(): Account
{
    return Account::factory()->create([
        'code' => '4800-FE',
        'name' => 'إيرادات متنوعة',
        'type' => AccountType::Income,
        'is_active' => true,
    ]);
}

it('posts journal entry for farm expense (debit expense, credit treasury)', function () {
    $user = User::factory()->create();
    $farm = Farm::factory()->create();
    $treasury = createFarmExpenseTreasuryAccount();
    $expense = createFarmExpenseAccount();

    $farmExpense = FarmExpense::create([
        'farm_id' => $farm->id,
        'type' => FarmExpenseType::Expense,
        'treasury_account_id' => $treasury->id,
        'account_id' => $expense->id,
        'amount' => 5000,
        'date' => now()->toDateString(),
        'description' => 'إيجار المزرعة',
        'created_by' => $user->id,
    ]);

    expect($farmExpense->journal_entry_id)->not()->toBeNull();

    $entry = JournalEntry::find($farmExpense->journal_entry_id);
    expect($entry)->not()->toBeNull();
    expect($entry->isBalanced())->toBeTrue();

    $lines = JournalLine::where('journal_entry_id', $entry->id)->get();
    expect($lines)->toHaveCount(2);

    $debit = $lines->firstWhere('debit', '>', 0);
    $credit = $lines->firstWhere('credit', '>', 0);

    expect((int) $debit->debit)->toBe(5000);
    expect((int) $credit->credit)->toBe(5000);
    expect($debit->account_id)->toBe($expense->id);
    expect($credit->account_id)->toBe($treasury->id);
});

it('posts journal entry for farm revenue (debit treasury, credit income)', function () {
    $user = User::factory()->create();
    $farm = Farm::factory()->create();
    $treasury = createFarmExpenseTreasuryAccount();
    $income = createFarmIncomeAccount();

    $farmExpense = FarmExpense::create([
        'farm_id' => $farm->id,
        'type' => FarmExpenseType::Revenue,
        'treasury_account_id' => $treasury->id,
        'account_id' => $income->id,
        'amount' => 3000,
        'date' => now()->toDateString(),
        'description' => 'إيراد تأجير معدات',
        'created_by' => $user->id,
    ]);

    expect($farmExpense->journal_entry_id)->not()->toBeNull();

    $entry = JournalEntry::find($farmExpense->journal_entry_id);
    expect($entry)->not()->toBeNull();
    expect($entry->isBalanced())->toBeTrue();

    $lines = JournalLine::where('journal_entry_id', $entry->id)->get();
    expect($lines)->toHaveCount(2);

    $debit = $lines->firstWhere('debit', '>', 0);
    $credit = $lines->firstWhere('credit', '>', 0);

    expect((int) $debit->debit)->toBe(3000);
    expect((int) $credit->credit)->toBe(3000);
    expect($debit->account_id)->toBe($treasury->id);
    expect($credit->account_id)->toBe($income->id);
});

it('creates farm expense with all relationships', function () {
    $user = User::factory()->create();
    $farm = Farm::factory()->create();
    $treasury = createFarmExpenseTreasuryAccount();
    $expense = createFarmExpenseAccount();

    $farmExpense = FarmExpense::create([
        'farm_id' => $farm->id,
        'type' => FarmExpenseType::Expense,
        'treasury_account_id' => $treasury->id,
        'account_id' => $expense->id,
        'amount' => 1500,
        'date' => now()->toDateString(),
        'description' => 'صيانة عامة',
        'created_by' => $user->id,
    ]);

    $farmExpense->load(['farm', 'treasuryAccount', 'account', 'createdBy']);

    expect($farmExpense->farm->id)->toBe($farm->id);
    expect($farmExpense->treasuryAccount->id)->toBe($treasury->id);
    expect($farmExpense->account->id)->toBe($expense->id);
    expect($farmExpense->createdBy->id)->toBe($user->id);
    expect($farmExpense->type)->toBe(FarmExpenseType::Expense);
});

it('belongs to farm via farmExpenses relationship', function () {
    $farm = Farm::factory()->create();
    $treasury = createFarmExpenseTreasuryAccount();
    $expense = createFarmExpenseAccount();

    FarmExpense::create([
        'farm_id' => $farm->id,
        'type' => FarmExpenseType::Expense,
        'treasury_account_id' => $treasury->id,
        'account_id' => $expense->id,
        'amount' => 2000,
        'date' => now()->toDateString(),
    ]);

    FarmExpense::create([
        'farm_id' => $farm->id,
        'type' => FarmExpenseType::Revenue,
        'treasury_account_id' => $treasury->id,
        'account_id' => $expense->id,
        'amount' => 800,
        'date' => now()->toDateString(),
    ]);

    expect($farm->farmExpenses)->toHaveCount(2);
});
