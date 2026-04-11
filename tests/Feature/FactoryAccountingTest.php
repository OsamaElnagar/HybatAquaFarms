<?php

use App\Enums\FeedMovementType;
use App\Models\Account;
use App\Models\Factory;
use App\Models\FeedItem;
use App\Models\FeedMovement;
use App\Models\FeedWarehouse;
use App\Models\JournalLine;
use Database\Seeders\AccountSeeder;
use Database\Seeders\PostingRuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(AccountSeeder::class);
    $this->seed(PostingRuleSeeder::class);
});

test('feed purchase is posted to the factory-specific account', function () {
    // 1. Setup: Create a factory with a specific account
    $account = Account::factory()->create([
        'code' => 'TEST-FAC-01',
        'name' => 'Specific Factory Account',
    ]);

    $factory = Factory::factory()->create([
        'account_id' => $account->id,
    ]);

    $warehouse = FeedWarehouse::factory()->create();
    $feedItem = FeedItem::factory()->create();

    // 2. Action: Create a feed purchase (IN movement)
    $movement = FeedMovement::create([
        'movement_type' => FeedMovementType::In,
        'factory_id' => $factory->id,
        'feed_item_id' => $feedItem->id,
        'to_warehouse_id' => $warehouse->id,
        'quantity' => 100,
        'total_cost' => 5000,
        'date' => now(),
    ]);

    // 3. Assert: Check if a journal line was created with the factory's account
    $line = JournalLine::where('account_id', $account->id)
        ->where('credit', 5000)
        ->first();

    expect($line)->not->toBeNull()
        ->and($line->journalEntry->source_id)->toBe($movement->id);
});

test('factory payment is posted to the factory-specific account', function () {
    // 1. Setup: Create a factory with a specific account
    $account = Account::factory()->create([
        'code' => 'TEST-FAC-02',
        'name' => 'Specific Factory Account 2',
    ]);

    $factory = Factory::factory()->create([
        'account_id' => $account->id,
    ]);

    // 2. Action: Create a factory payment
    $payment = $factory->factoryPayments()->create([
        'amount' => 2000,
        'date' => now(),
        'description' => 'Test payment',
    ]);

    // 3. Assert: Check if a journal line was created with the factory's account
    $lines = JournalLine::all();
    if ($lines->isEmpty()) {
        // dump('No journal lines created at all');
    }

    $line = JournalLine::where('account_id', $account->id)
        ->where('debit', 2000)
        ->first();

    if (! $line) {
        // dump('Journal lines found:', JournalLine::with('journalEntry')->get()->map(fn($l) => ['acc' => $l->account_id, 'deb' => $l->debit, 'src' => $l->journalEntry->source_type])->toArray());
    }

    expect($line)->not->toBeNull()
        ->and($line->journalEntry->source_id)->toBe($payment->id);
});
