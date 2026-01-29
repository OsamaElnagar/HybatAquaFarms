<?php

declare(strict_types=1);

use App\Enums\AccountType;
use App\Enums\ExternalCalculationType;
use App\Models\Account;
use App\Models\ExternalCalculation;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTreasuryAccount(): Account
{
    return Account::factory()->create([
        'code' => '1110',
        'name' => 'النقدية بالصندوق',
        'type' => AccountType::Asset,
        'is_treasury' => true,
        'is_active' => true,
    ]);
}

function createExpenseAccount(): Account
{
    return Account::factory()->create([
        'code' => '5280',
        'name' => 'مصروفات متنوعة',
        'type' => AccountType::Expense,
        'is_active' => true,
    ]);
}

function createIncomeAccount(): Account
{
    return Account::factory()->create([
        'code' => '4800',
        'name' => 'إيرادات متنوعة',
        'type' => AccountType::Income,
        'is_active' => true,
    ]);
}

it('posts journal for payment (expense vs treasury)', function () {
    $user = User::factory()->create();
    $treasury = createTreasuryAccount();
    $expense = createExpenseAccount();

    $calc = ExternalCalculation::create([
        'type' => ExternalCalculationType::Payment,
        'treasury_account_id' => $treasury->id,
        'account_id' => $expense->id,
        'amount' => 1000,
        'date' => now()->toDateString(),
        'created_by' => $user->id,
    ]);

    expect($calc->journal_entry_id)->not()->toBeNull();
    $entry = JournalEntry::find($calc->journal_entry_id);
    expect($entry)->not()->toBeNull();
    expect($entry->isBalanced())->toBeTrue();

    $lines = JournalLine::where('journal_entry_id', $entry->id)->get();
    expect($lines)->toHaveCount(2);

    $debit = $lines->firstWhere('debit', '>', 0);
    $credit = $lines->firstWhere('credit', '>', 0);

    expect((int) $debit->debit)->toBe(1000);
    expect((int) $credit->credit)->toBe(1000);
    expect($debit->account_id)->toBe($expense->id);
    expect($credit->account_id)->toBe($treasury->id);
});

it('posts journal for receipt (treasury vs income)', function () {
    $user = User::factory()->create();
    $treasury = createTreasuryAccount();
    $income = createIncomeAccount();

    $calc = ExternalCalculation::create([
        'type' => ExternalCalculationType::Receipt,
        'treasury_account_id' => $treasury->id,
        'account_id' => $income->id,
        'amount' => 2000,
        'date' => now()->toDateString(),
        'created_by' => $user->id,
    ]);

    expect($calc->journal_entry_id)->not()->toBeNull();
    $entry = JournalEntry::find($calc->journal_entry_id);
    expect($entry)->not()->toBeNull();
    expect($entry->isBalanced())->toBeTrue();

    $lines = JournalLine::where('journal_entry_id', $entry->id)->get();
    expect($lines)->toHaveCount(2);

    $debit = $lines->firstWhere('debit', '>', 0);
    $credit = $lines->firstWhere('credit', '>', 0);

    expect((int) $debit->debit)->toBe(2000);
    expect((int) $credit->credit)->toBe(2000);
    expect($debit->account_id)->toBe($treasury->id);
    expect($credit->account_id)->toBe($income->id);
});
