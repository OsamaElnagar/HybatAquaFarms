<?php

declare(strict_types=1);

use App\Enums\PaymentStatus;
use App\Enums\VoucherType;
use App\Models\Account;
use App\Models\Farm;
use App\Models\JournalLine;
use App\Models\PostingRule;
use App\Models\SalesOrder;
use App\Models\Trader;
use App\Models\User;
use App\Models\Voucher;
use App\Services\TreasuryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupAccountsAndRules(Farm $farm): array
{
    $treasury = Account::factory()->create([
        'code' => '1120',
        'name' => 'Petty Cash',
        'type' => 'asset',
        'is_treasury' => true,
        'farm_id' => $farm->id,
    ]);

    $receivable = Account::factory()->create([
        'code' => '1140',
        'name' => 'Accounts Receivable',
        'type' => 'asset',
        'farm_id' => $farm->id,
    ]);

    $sales = Account::factory()->create([
        'code' => '4100',
        'name' => 'Sales Revenue',
        'type' => 'income',
        'farm_id' => $farm->id,
    ]);

    PostingRule::query()->updateOrCreate(
        ['event_key' => 'sales.credit'],
        [
            'description' => 'Credit sales',
            'debit_account_id' => $receivable->id,
            'credit_account_id' => $sales->id,
            'options' => null,
            'is_active' => true,
        ]
    );

    PostingRule::query()->updateOrCreate(
        ['event_key' => 'voucher.receipt'],
        [
            'description' => 'Cash receipt',
            'debit_account_id' => $treasury->id,
            'credit_account_id' => $sales->id,
            'options' => null,
            'is_active' => true,
        ]
    );

    return [$treasury, $receivable, $sales];
}

test('cash sale with paid status creates voucher and updates treasury only', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $farm = Farm::factory()->create();

    [$treasury, $receivable, $sales] = setupAccountsAndRules($farm);

    $harvestOperation = \App\Models\HarvestOperation::create([
        'operation_number' => 'OP-TEST-'.rand(1000, 9999),
        'batch_id' => \App\Models\Batch::factory()->create(['farm_id' => $farm->id])->id,
        'farm_id' => $farm->id,
        'start_date' => now(),
        'status' => 'ongoing',
    ]);

    $trader = Trader::factory()->create();

    $netAmount = 1000.00;

    $salesOrder = SalesOrder::create([
        'harvest_operation_id' => $harvestOperation->id,
        'trader_id' => $trader->id,
        'date' => now(),
        'boxes_subtotal' => $netAmount,
        'net_amount' => $netAmount,
        'payment_status' => PaymentStatus::Paid,
        'created_by' => $user->id,
    ]);

    $salesOrder->refresh();

    $voucher = Voucher::create([
        'farm_id' => $farm->id,
        'voucher_type' => VoucherType::Receipt,
        'date' => $salesOrder->date,
        'counterparty_type' => Trader::class,
        'counterparty_id' => $salesOrder->trader_id,
        'treasury_account_id' => $treasury->id,
        'account_id' => $sales->id,
        'amount' => $netAmount,
        'description' => "تحصيل فوري - أمر بيع رقم {$salesOrder->order_number}",
        'created_by' => $user->id,
    ]);

    $voucher->refresh();

    $treasuryService = new TreasuryService;
    $balance = $treasuryService->getTreasuryBalance($farm);

    expect($balance)->toBe((float) $netAmount);

    $cashLines = JournalLine::query()
        ->where('account_id', $treasury->id)
        ->get();

    $salesLines = JournalLine::query()
        ->where('account_id', $sales->id)
        ->get();

    expect($cashLines->sum('debit'))->toBe((float) $netAmount)
        ->and($cashLines->sum('credit'))->toBe(0.0)
        ->and($salesLines->sum('credit'))->toBe((float) $netAmount)
        ->and($salesLines->sum('debit'))->toBe(0.0);

    $orderLines = JournalLine::query()
        ->where('source_type', SalesOrder::class)
        ->count();

    expect($orderLines)->toBe(0);
});

test('credit sale posts receivable and sales without touching treasury', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $farm = Farm::factory()->create();

    [$treasury, $receivable, $sales] = setupAccountsAndRules($farm);

    $harvestOperation = \App\Models\HarvestOperation::create([
        'operation_number' => 'OP-TEST-'.rand(1000, 9999),
        'batch_id' => \App\Models\Batch::factory()->create(['farm_id' => $farm->id])->id,
        'farm_id' => $farm->id,
        'start_date' => now(),
        'status' => 'ongoing',
    ]);

    $trader = Trader::factory()->create();

    $netAmount = 1500.00;

    $salesOrder = SalesOrder::create([
        'harvest_operation_id' => $harvestOperation->id,
        'trader_id' => $trader->id,
        'date' => now(),
        'boxes_subtotal' => $netAmount,
        'net_amount' => $netAmount,
        'payment_status' => PaymentStatus::Pending,
        'created_by' => $user->id,
    ]);

    $salesOrder->refresh();

    $treasuryService = new TreasuryService;
    $balance = $treasuryService->getTreasuryBalance($farm);

    expect($balance)->toBe(0.0);

    $receivableLines = JournalLine::query()
        ->where('account_id', $receivable->id)
        ->get();

    $salesLines = JournalLine::query()
        ->where('account_id', $sales->id)
        ->get();

    expect($receivableLines->sum('debit'))->toBe((float) $netAmount)
        ->and($receivableLines->sum('credit'))->toBe(0.0)
        ->and($salesLines->sum('credit'))->toBe((float) $netAmount)
        ->and($salesLines->sum('debit'))->toBe(0.0);

    $treasuryLines = JournalLine::query()
        ->where('account_id', $treasury->id)
        ->count();

    expect($treasuryLines)->toBe(0);
});
