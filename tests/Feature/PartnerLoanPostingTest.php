<?php

use App\Enums\RepaymentType;
use App\Models\Account;
use App\Models\Factory as FactoryModel;
use App\Models\PartnerLoan;
use App\Models\PartnerLoanRepayment;
use App\Models\Trader;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\PostingRuleSeeder;

beforeEach(function () {
    $this->seed(AccountSeeder::class);
    $this->seed(PostingRuleSeeder::class);
});

test('trader loan creates correct journal entry', function () {
    $trader = Trader::factory()->create();
    $user = User::factory()->create();
    $treasury = Account::where('code', '1110')->first();

    $loan = PartnerLoan::create([
        'loanable_type' => Trader::class,
        'loanable_id' => $trader->id,
        'date' => now(),
        'amount' => 10000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'description' => 'سلفة من تاجر',
        'created_by' => $user->id,
    ]);

    expect($loan->journal_entry_id)->not->toBeNull();
    expect($loan->journalEntry)->not->toBeNull();

    $entry = $loan->journalEntry;
    expect($entry->is_posted)->toBeTrue();
    expect($entry->isBalanced())->toBeTrue();
    expect($entry->source_type)->toBe(PartnerLoan::class);

    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    // Debit should be Treasury (1110)
    expect($debitLine->account_id)->toBe($treasury->id);
    expect((float) $debitLine->debit)->toEqual(10000.00);

    // Credit should be Partner Loans Payable (2130)
    $loansPayable = Account::where('code', '2130')->first();
    expect($creditLine->account_id)->toBe($loansPayable->id);
    expect((float) $creditLine->credit)->toEqual(10000.00);
});

test('factory loan creates correct journal entry', function () {
    $factory = FactoryModel::factory()->create();
    $user = User::factory()->create();
    $treasury = Account::where('code', '1110')->first();

    $loan = PartnerLoan::create([
        'loanable_type' => FactoryModel::class,
        'loanable_id' => $factory->id,
        'date' => now(),
        'amount' => 5000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'description' => 'سلفة من مصنع',
        'created_by' => $user->id,
    ]);

    expect($loan->journal_entry_id)->not->toBeNull();

    $entry = $loan->journalEntry;
    expect($entry->isBalanced())->toBeTrue();

    $loansPayable = Account::where('code', '2130')->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();
    expect($creditLine->account_id)->toBe($loansPayable->id);
});

test('cash repayment creates correct journal entry', function () {
    $trader = Trader::factory()->create();
    $user = User::factory()->create();
    $treasury = Account::where('code', '1110')->first();

    $loan = PartnerLoan::create([
        'loanable_type' => Trader::class,
        'loanable_id' => $trader->id,
        'date' => now(),
        'amount' => 10000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    $repayment = PartnerLoanRepayment::create([
        'partner_loan_id' => $loan->id,
        'repayment_type' => RepaymentType::Cash,
        'date' => now(),
        'amount' => 3000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    expect($repayment->journal_entry_id)->not->toBeNull();

    $entry = $repayment->journalEntry;
    expect($entry->isBalanced())->toBeTrue();

    $loansPayable = Account::where('code', '2130')->first();
    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    // Debit: Partner Loans Payable (2130)
    expect($debitLine->account_id)->toBe($loansPayable->id);
    expect((float) $debitLine->debit)->toEqual(3000.00);

    // Credit: Treasury (1110)
    expect($creditLine->account_id)->toBe($treasury->id);
    expect((float) $creditLine->credit)->toEqual(3000.00);
});

test('trader netting repayment creates correct journal entry', function () {
    $trader = Trader::factory()->create();
    $user = User::factory()->create();
    $treasury = Account::where('code', '1110')->first();

    $loan = PartnerLoan::create([
        'loanable_type' => Trader::class,
        'loanable_id' => $trader->id,
        'date' => now(),
        'amount' => 10000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    $repayment = PartnerLoanRepayment::create([
        'partner_loan_id' => $loan->id,
        'repayment_type' => RepaymentType::Netting,
        'date' => now(),
        'amount' => 5000.00,
        'description' => 'مقاصة من مبيعات',
        'created_by' => $user->id,
    ]);

    expect($repayment->journal_entry_id)->not->toBeNull();

    $entry = $repayment->journalEntry;
    expect($entry->isBalanced())->toBeTrue();

    $loansPayable = Account::where('code', '2130')->first();
    $traderReceivables = Account::where('code', '1140')->first();

    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    // Dr 2130 / Cr 1140
    expect($debitLine->account_id)->toBe($loansPayable->id);
    expect($creditLine->account_id)->toBe($traderReceivables->id);
});

test('factory netting repayment creates correct journal entry', function () {
    $factory = FactoryModel::factory()->create();
    $user = User::factory()->create();
    $treasury = Account::where('code', '1110')->first();

    $loan = PartnerLoan::create([
        'loanable_type' => FactoryModel::class,
        'loanable_id' => $factory->id,
        'date' => now(),
        'amount' => 8000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    $repayment = PartnerLoanRepayment::create([
        'partner_loan_id' => $loan->id,
        'repayment_type' => RepaymentType::Netting,
        'date' => now(),
        'amount' => 4000.00,
        'description' => 'مقاصة من مستحقات مصنع',
        'created_by' => $user->id,
    ]);

    $entry = $repayment->journalEntry;
    expect($entry->isBalanced())->toBeTrue();

    $loansPayable = Account::where('code', '2130')->first();
    $factoryPayables = Account::where('code', '2110')->first();

    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    // Dr 2130 / Cr 2110
    expect($debitLine->account_id)->toBe($loansPayable->id);
    expect($creditLine->account_id)->toBe($factoryPayables->id);
});

test('remaining balance is calculated correctly', function () {
    $trader = Trader::factory()->create();
    $user = User::factory()->create();
    $treasury = Account::where('code', '1110')->first();

    $loan = PartnerLoan::create([
        'loanable_type' => Trader::class,
        'loanable_id' => $trader->id,
        'date' => now(),
        'amount' => 10000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    expect($loan->remaining_balance)->toEqual(10000.00);

    PartnerLoanRepayment::create([
        'partner_loan_id' => $loan->id,
        'repayment_type' => RepaymentType::Cash,
        'date' => now(),
        'amount' => 3000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    $loan->refresh();
    expect($loan->remaining_balance)->toEqual(7000.00);

    PartnerLoanRepayment::create([
        'partner_loan_id' => $loan->id,
        'repayment_type' => RepaymentType::Netting,
        'date' => now(),
        'amount' => 7000.00,
        'description' => 'مقاصة',
        'created_by' => $user->id,
    ]);

    $loan->refresh();
    expect($loan->remaining_balance)->toEqual(0.0);
});

test('trader partner loans balance attribute works', function () {
    $trader = Trader::factory()->create();
    $user = User::factory()->create();
    $treasury = Account::where('code', '1110')->first();

    PartnerLoan::create([
        'loanable_type' => Trader::class,
        'loanable_id' => $trader->id,
        'date' => now(),
        'amount' => 10000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    $secondLoan = PartnerLoan::create([
        'loanable_type' => Trader::class,
        'loanable_id' => $trader->id,
        'date' => now(),
        'amount' => 5000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    PartnerLoanRepayment::create([
        'partner_loan_id' => $secondLoan->id,
        'repayment_type' => RepaymentType::Cash,
        'date' => now(),
        'amount' => 2000.00,
        'payment_method' => 'cash',
        'treasury_account_id' => $treasury->id,
        'created_by' => $user->id,
    ]);

    $trader->refresh();
    // 10000 + (5000 - 2000) = 13000
    expect($trader->partner_loans_balance)->toEqual(13000.00);
});
