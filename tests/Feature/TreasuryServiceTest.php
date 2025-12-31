<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Farm;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\TreasuryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new TreasuryService;
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->farm = Farm::factory()->create();

    // Create test accounts
    $this->treasuryAccount = Account::factory()->create([
        'code' => 'CASH-001',
        'name' => 'Main Cash Account',
        'type' => 'asset',
        'is_treasury' => true,
        'farm_id' => $this->farm->id,
    ]);

    $this->revenueAccount = Account::factory()->create([
        'code' => 'REVENUE',
        'name' => 'Sales Revenue',
        'type' => 'income',
        'farm_id' => $this->farm->id,
    ]);

    $this->expenseAccount = Account::factory()->create([
        'code' => 'EXPENSE',
        'name' => 'Operating Expenses',
        'type' => 'expense',
        'farm_id' => $this->farm->id,
    ]);
});

test('treasury balance starts at zero', function () {
    $balance = $this->service->getTreasuryBalance($this->farm);

    expect($balance)->toBe(0.0);
});

test('recording a receipt increases treasury balance', function () {
    $source = JournalEntry::factory()->create();

    $this->service->recordReceipt(
        treasuryAccount: $this->treasuryAccount,
        amount: 1000.00,
        description: 'Fish sale payment',
        source: $source,
        revenueAccount: $this->revenueAccount
    );

    $balance = $this->service->getTreasuryBalance($this->farm);

    expect($balance)->toBe(1000.0);
});

test('recording a payment decreases treasury balance', function () {
    // First, add some money
    $source1 = JournalEntry::factory()->create();
    $this->service->recordReceipt(
        $this->treasuryAccount,
        2000.00,
        'Initial cash',
        $source1,
        $this->revenueAccount
    );

    // Then, make a payment
    $source2 = JournalEntry::factory()->create();
    $this->service->recordPayment(
        $this->treasuryAccount,
        500.00,
        'Feed purchase',
        $source2,
        $this->expenseAccount
    );

    $balance = $this->service->getTreasuryBalance($this->farm);

    expect($balance)->toBe(1500.0);
});

test('multiple transactions are calculated correctly', function () {
    $source1 = JournalEntry::factory()->create();
    $source2 = JournalEntry::factory()->create();
    $source3 = JournalEntry::factory()->create();

    // Receipt: +5000
    $this->service->recordReceipt(
        $this->treasuryAccount,
        5000.00,
        'Fish sale 1',
        $source1,
        $this->revenueAccount
    );

    // Payment: -1000
    $this->service->recordPayment(
        $this->treasuryAccount,
        1000.00,
        'Feed purchase',
        $source2,
        $this->expenseAccount
    );

    // Receipt: +2000
    $this->service->recordReceipt(
        $this->treasuryAccount,
        2000.00,
        'Fish sale 2',
        $source3,
        $this->revenueAccount
    );

    $balance = $this->service->getTreasuryBalance($this->farm);

    expect($balance)->toBe(6000.0); // 5000 - 1000 + 2000
});

test('account balance is calculated from journal lines', function () {
    $source = JournalEntry::factory()->create();

    $this->service->recordReceipt(
        $this->treasuryAccount,
        3000.00,
        'Test receipt',
        $source,
        $this->revenueAccount
    );

    $this->treasuryAccount->refresh();

    expect($this->treasuryAccount->balance)->toBe(3000.0);
});

test('daily summary is calculated correctly', function () {
    $source1 = JournalEntry::factory()->create();
    $source2 = JournalEntry::factory()->create();

    $this->service->recordReceipt(
        $this->treasuryAccount,
        4000.00,
        'Receipt today',
        $source1,
        $this->revenueAccount,
        ['date' => today()]
    );

    $this->service->recordPayment(
        $this->treasuryAccount,
        1500.00,
        'Payment today',
        $source2,
        $this->expenseAccount,
        ['date' => today()]
    );

    $summary = $this->service->getDailySummary($this->farm, today()->toDateString());

    expect($summary['incoming'])->toBe(4000.0)
        ->and($summary['outgoing'])->toBe(1500.0)
        ->and($summary['net'])->toBe(2500.0);
});

test('transactions from different farms are isolated', function () {
    $farm2 = Farm::factory()->create();
    $treasury2 = Account::factory()->create([
        'code' => 'CASH-002',
        'name' => 'Farm 2 Cash',
        'type' => 'asset',
        'is_treasury' => true,
        'farm_id' => $farm2->id,
    ]);

    $revenue2 = Account::factory()->create([
        'code' => 'REV-002',
        'name' => 'Farm 2 Revenue',
        'type' => 'income',
        'farm_id' => $farm2->id,
    ]);

    $source1 = JournalEntry::factory()->create();
    $source2 = JournalEntry::factory()->create();

    // Farm 1 receipt
    $this->service->recordReceipt(
        $this->treasuryAccount,
        1000.00,
        'Farm 1 sale',
        $source1,
        $this->revenueAccount
    );

    // Farm 2 receipt
    $this->service->recordReceipt(
        $treasury2,
        3000.00,
        'Farm 2 sale',
        $source2,
        $revenue2
    );

    $balance1 = $this->service->getTreasuryBalance($this->farm);
    $balance2 = $this->service->getTreasuryBalance($farm2);

    expect($balance1)->toBe(1000.0)
        ->and($balance2)->toBe(3000.0);
});

test('journal entries are balanced', function () {
    $source = JournalEntry::factory()->create();

    $entry = $this->service->recordReceipt(
        $this->treasuryAccount,
        2500.00,
        'Test balanced entry',
        $source,
        $this->revenueAccount
    );

    $entry->refresh();

    expect($entry->isBalanced())->toBeTrue()
        ->and($entry->total_debit)->toBe($entry->total_credit)
        ->and($entry->total_debit)->toBe(2500.0);
});

test('get transactions returns correct records', function () {
    $source1 = JournalEntry::factory()->create();
    $source2 = JournalEntry::factory()->create();

    $this->service->recordReceipt(
        $this->treasuryAccount,
        1000.00,
        'Receipt 1',
        $source1,
        $this->revenueAccount
    );

    $this->service->recordPayment(
        $this->treasuryAccount,
        500.00,
        'Payment 1',
        $source2,
        $this->expenseAccount
    );

    $transactions = $this->service->getTransactions($this->farm);

    expect($transactions)->toHaveCount(2);
});

test('balance as of specific date works correctly', function () {
    $source1 = JournalEntry::factory()->create();
    $source2 = JournalEntry::factory()->create();

    // Transaction yesterday
    $this->service->recordReceipt(
        $this->treasuryAccount,
        1000.00,
        'Yesterday receipt',
        $source1,
        $this->revenueAccount,
        ['date' => today()->subDay()]
    );

    // Transaction today
    $this->service->recordReceipt(
        $this->treasuryAccount,
        500.00,
        'Today receipt',
        $source2,
        $this->revenueAccount,
        ['date' => today()]
    );

    $this->treasuryAccount->refresh();

    $balanceYesterday = $this->treasuryAccount->getBalanceAsOf(today()->subDay()->toDateString());
    $balanceToday = $this->treasuryAccount->getBalanceAsOf(today()->toDateString());

    expect($balanceYesterday)->toBe(1000.0)
        ->and($balanceToday)->toBe(1500.0);
});

test('vouchers automatically create journal entries with selected accounts', function () {
    $categoryAccount = Account::factory()->create([
        'name' => 'Feed Expenses',
        'type' => 'expense',
        'is_treasury' => false,
        'farm_id' => $this->farm->id,
    ]);

    // Create posting rules
    \App\Models\PostingRule::factory()->create([
        'event_key' => 'voucher.payment',
        'debit_account_id' => $categoryAccount->id,
        'credit_account_id' => $this->treasuryAccount->id,
    ]);

    // Create a payment voucher
    $voucher = \App\Models\Voucher::create([
        'farm_id' => $this->farm->id,
        'voucher_type' => \App\Enums\VoucherType::Payment,
        'voucher_number' => 'V-TEST-001',
        'date' => now(),
        'amount' => 750.00,
        'treasury_account_id' => $this->treasuryAccount->id,
        'account_id' => $categoryAccount->id,
        'counterparty_type' => 'App\Models\Factory',
        'counterparty_id' => 1,
        'description' => 'Test payment flow',
    ]);

    // Check if Journal Entry exists
    $entry = \App\Models\JournalEntry::where('source_type', $voucher->getMorphClass())
        ->where('source_id', $voucher->id)
        ->first();

    expect($entry)->not->toBeNull()
        ->and($entry->isBalanced())->toBeTrue();

    // Verify lines
    $treasuryLine = $entry->lines()->where('account_id', $this->treasuryAccount->id)->first();
    $categoryLine = $entry->lines()->where('account_id', $categoryAccount->id)->first();

    // For Payment: Debit Category (Expense), Credit Treasury (Asset)
    expect((float) $categoryLine->debit)->toBe(750.00)
        ->and((float) $treasuryLine->credit)->toBe(750.00);

    // Verify Treasury Balance
    expect($this->service->getTreasuryBalance($this->farm))->toBe(-750.00);
});
