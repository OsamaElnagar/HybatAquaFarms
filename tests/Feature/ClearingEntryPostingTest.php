<?php

use App\Models\Account;
use App\Models\ClearingEntry;
use App\Models\Factory as FactoryModel;
use App\Models\Trader;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\PostingRuleSeeder;

beforeEach(function () {
    $this->seed(AccountSeeder::class);
    $this->seed(PostingRuleSeeder::class);
});

test('clearing entry creates correct journal entry', function () {
    $trader = Trader::factory()->create();
    $factory = FactoryModel::factory()->create();
    $user = User::factory()->create();

    $clearingEntry = ClearingEntry::create([
        'trader_id' => $trader->id,
        'factory_id' => $factory->id,
        'date' => now(),
        'amount' => 5000.00,
        'description' => 'تسوية بين تاجر ومصنع',
        'created_by' => $user->id,
    ]);

    // Should have journal entry linked
    expect($clearingEntry->journal_entry_id)->not->toBeNull();
    expect($clearingEntry->journalEntry)->not->toBeNull();

    $entry = $clearingEntry->journalEntry;

    expect($entry->is_posted)->toBeTrue();
    expect($entry->isBalanced())->toBeTrue();
    expect($entry->source_type)->toBe(ClearingEntry::class);
    expect($entry->source_id)->toBe($clearingEntry->id);

    // Should have 2 lines
    expect($entry->lines)->toHaveCount(2);

    $debitLine = $entry->lines->where('debit', '>', 0)->first();
    $creditLine = $entry->lines->where('credit', '>', 0)->first();

    // Debit should be Factory Payables account (2110)
    $payablesAccount = Account::where('code', '2110')->first();
    expect($debitLine->account_id)->toBe($payablesAccount->id);
    expect((float) $debitLine->debit)->toEqual(5000.00);

    // Credit should be Trader Receivables account (1140)
    $receivablesAccount = Account::where('code', '1140')->first();
    expect($creditLine->account_id)->toBe($receivablesAccount->id);
    expect((float) $creditLine->credit)->toEqual(5000.00);
});
