<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Models\Voucher;
use App\Services\TreasuryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DebugSalesOrders extends Command
{
    protected $signature = 'debug:sales-orders {salesOrder}';

    protected $description = 'Inspect accounting and treasury impact for a specific sales order';

    public function handle()
    {
        $salesOrderId = (int) $this->argument('salesOrder');

        $order = SalesOrder::with([
            'harvestOperation.farm',
            'trader',
            'journalEntries.lines.account',
        ])->find($salesOrderId);

        if (! $order) {
            $this->error('Sales order not found');
            Log::warning('debug:sales-orders - sales order not found', ['sales_order_id' => $salesOrderId]);

            return static::FAILURE;
        }

        $farm = $order->harvestOperation?->farm;

        $this->info('=== Sales Order ===');
        $this->line('ID: '.$order->id);
        $this->line('Order Number: '.$order->order_number);
        $this->line('Date: '.$order->date?->toDateString());
        $this->line('Payment Status: '.(string) $order->payment_status?->value);
        $this->line('Net Amount: '.(float) $order->net_amount);

        if ($farm) {
            $this->line('Farm: '.$farm->name.' (ID: '.$farm->id.')');
        }

        $this->newLine();
        $this->info('=== Journal Entries linked to Sales Order ===');

        if ($order->journalEntries->isEmpty()) {
            $this->warn('No journal entries directly linked to this sales order.');
        } else {
            foreach ($order->journalEntries as $entry) {
                $this->line('Entry #'.$entry->entry_number.' on '.$entry->date?->toDateString());
                $this->line('Description: '.$entry->description);

                foreach ($entry->lines as $line) {
                    $accountName = $line->account?->name ?? ('Account ID '.$line->account_id);
                    $this->line(
                        '  - '.$accountName.
                        ' | Debit: '.(float) $line->debit.
                        ' | Credit: '.(float) $line->credit
                    );
                }

                $this->newLine();
            }
        }

        $this->info('=== Vouchers (by trader and order number in description) ===');

        $vouchersQuery = Voucher::query()
            ->where('counterparty_type', get_class($order->trader))
            ->where('counterparty_id', $order->trader_id);

        if ($order->order_number) {
            $vouchersQuery->where('description', 'like', '%'.$order->order_number.'%');
        }

        $vouchers = $vouchersQuery->get();

        if ($vouchers->isEmpty()) {
            $this->warn('No vouchers matched this sales order by trader and description.');
        } else {
            foreach ($vouchers as $voucher) {
                $this->line('Voucher #'.$voucher->voucher_number.' (ID: '.$voucher->id.')');
                $this->line('Type: '.$voucher->voucher_type?->value.' | Amount: '.(float) $voucher->amount);
                $this->line('Date: '.$voucher->date?->toDateString());
                $this->line('Description: '.$voucher->description);

                $entry = JournalEntry::query()
                    ->where('source_type', $voucher->getMorphClass())
                    ->where('source_id', $voucher->id)
                    ->with('lines.account')
                    ->first();

                if ($entry) {
                    $this->line('  Linked Journal Entry: '.$entry->entry_number);

                    foreach ($entry->lines as $line) {
                        $accountName = $line->account?->name ?? ('Account ID '.$line->account_id);
                        $this->line(
                            '    - '.$accountName.
                            ' | Debit: '.(float) $line->debit.
                            ' | Credit: '.(float) $line->credit
                        );
                    }
                } else {
                    $this->warn('  No journal entry found for this voucher.');
                }

                $this->newLine();
            }
        }

        if ($farm) {
            $treasuryService = app(TreasuryService::class);
            $balance = $treasuryService->getTreasuryBalance($farm);

            $this->info('=== Treasury Summary for Farm ===');
            $this->line('Current treasury balance: '.number_format($balance, 2));
        }

        Log::info('debug:sales-orders executed', [
            'sales_order_id' => $order->id,
            'farm_id' => $farm?->id,
            'net_amount' => (float) $order->net_amount,
        ]);

        return static::SUCCESS;
    }
}
