<?php

use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\Farm;
use App\Models\SalesOrder;
use App\Models\Trader;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\PostingRuleSeeder;

beforeEach(function () {
    // Seed accounts and posting rules before each test
    $this->seed(AccountSeeder::class);
    $this->seed(PostingRuleSeeder::class);
});

test('cash sale creates correct journal entry', function () {
    $farm = Farm::factory()->create();
    $trader = Trader::factory()->create();
    $user = User::factory()->create();

    $salesOrder = SalesOrder::factory()->create([
        'farm_id' => $farm->id,
        'trader_id' => $trader->id,
        'payment_status' => PaymentStatus::Paid,
        'total_amount' => 10000.00,
        'created_by' => $user->id,
    ]);

    // Should have one journal entry
    expect($salesOrder->journalEntries)->toHaveCount(1);

    $entry = $salesOrder->journalEntries->first();

    expect($entry->description)->toContain($salesOrder->order_number);
    expect($entry->is_posted)->toBeTrue();
    expect($entry->isBalanced())->toBeTrue();

    // Should have 2 lines (debit and credit)
    expect($entry->lines)->toHaveCount(2);

    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    // Debit should be Cash account (1110)
    $cashAccount = Account::where('code', '1110')->first();
    expect($debitLine->account_id)->toBe($cashAccount->id);
    expect((float) $debitLine->debit)->toEqual(10000.00);
    expect((float) $debitLine->credit)->toEqual(0.00);

    // Credit should be Sales Revenue account (4100)
    $salesAccount = Account::where('code', '4100')->first();
    expect($creditLine->account_id)->toBe($salesAccount->id);
    expect((float) $creditLine->debit)->toEqual(0.00);
    expect((float) $creditLine->credit)->toEqual(10000.00);
});

test('credit sale creates correct journal entry', function () {
    $farm = Farm::factory()->create();
    $trader = Trader::factory()->create();
    $user = User::factory()->create();

    $salesOrder = SalesOrder::factory()->create([
        'farm_id' => $farm->id,
        'trader_id' => $trader->id,
        'payment_status' => PaymentStatus::Pending,
        'total_amount' => 15000.00,
        'created_by' => $user->id,
    ]);

    // Should have one journal entry
    expect($salesOrder->journalEntries)->toHaveCount(1);

    $entry = $salesOrder->journalEntries->first();
    expect($entry->isBalanced())->toBeTrue();

    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    // Debit should be Receivables account (1140)
    $receivablesAccount = Account::where('code', '1140')->first();
    expect($debitLine->account_id)->toBe($receivablesAccount->id);
    expect((float) $debitLine->debit)->toEqual(15000.00);

    // Credit should be Sales Revenue account (4100)
    $salesAccount = Account::where('code', '4100')->first();
    expect($creditLine->account_id)->toBe($salesAccount->id);
    expect((float) $creditLine->credit)->toEqual(15000.00);
});

test('payment received creates payment journal entry', function () {
    $farm = Farm::factory()->create();
    $trader = Trader::factory()->create();
    $user = User::factory()->create();

    // Create credit sale (pending)
    $salesOrder = SalesOrder::factory()->create([
        'farm_id' => $farm->id,
        'trader_id' => $trader->id,
        'payment_status' => PaymentStatus::Pending,
        'total_amount' => 20000.00,
        'created_by' => $user->id,
    ]);

    // Should have 1 entry (credit sale)
    expect($salesOrder->journalEntries)->toHaveCount(1);

    // Update to paid
    $salesOrder->update([
        'payment_status' => PaymentStatus::Paid,
    ]);

    // Should now have 2 entries (credit sale + payment)
    $salesOrder->refresh();
    expect($salesOrder->journalEntries)->toHaveCount(2);

    // Get the payment entry (most recent)
    $paymentEntry = $salesOrder->journalEntries->sortByDesc('id')->first();
    expect($paymentEntry->isBalanced())->toBeTrue();

    $debitLine = $paymentEntry->lines->where('debit', '>', 0)->first();
    $creditLine = $paymentEntry->lines->where('credit', '>', 0)->first();

    // Debit should be Cash account (1110)
    $cashAccount = Account::where('code', '1110')->first();
    expect($debitLine->account_id)->toBe($cashAccount->id);
    expect((float) $debitLine->debit)->toEqual(20000.00);

    // Credit should be Receivables account (1140) - clearing the receivable
    $receivablesAccount = Account::where('code', '1140')->first();
    expect($creditLine->account_id)->toBe($receivablesAccount->id);
    expect((float) $creditLine->credit)->toEqual(20000.00);
});

test('partial payment status also creates credit sale entry', function () {
    $farm = Farm::factory()->create();
    $trader = Trader::factory()->create();
    $user = User::factory()->create();

    $salesOrder = SalesOrder::factory()->create([
        'farm_id' => $farm->id,
        'trader_id' => $trader->id,
        'payment_status' => PaymentStatus::Partial,
        'total_amount' => 12500.00,
        'created_by' => $user->id,
    ]);

    // Should create credit sale entry
    expect($salesOrder->journalEntries)->toHaveCount(1);

    $entry = $salesOrder->journalEntries->first();
    $debitLine = $entry->lines->where('debit', '>', 0)->first();

    // Should debit receivables (credit sale)
    $receivablesAccount = Account::where('code', '1140')->first();
    expect($debitLine->account_id)->toBe($receivablesAccount->id);
});
