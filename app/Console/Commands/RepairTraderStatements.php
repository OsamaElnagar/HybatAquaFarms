<?php

namespace App\Console\Commands;

use App\Domain\Accounting\PostingService;
use App\Enums\TraderStatementStatus;
use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Models\Trader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairTraderStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repair-trader-statements {--link-orphaned : Link journal entries without statement IDs to a session}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Backfill missing journal entries for SalesOrders and link orphaned entries to a trader statement session.';

    public function __construct(protected PostingService $posting)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $traders = Trader::all();
        $this->info("Starting repair for {$traders->count()} traders...");

        foreach ($traders as $trader) {
            $this->comment("Processing Trader: {$trader->name}");

            if (! $trader->account_id) {
                $this->warn("  Skipping {$trader->name}: No GL account assigned.");

                continue;
            }

            // Part 1: Backfill missing JournalEntries for SalesOrders
            $unpostedOrders = SalesOrder::where('trader_id', $trader->id)
                ->whereDoesntHave('journalEntries')
                ->get();

            foreach ($unpostedOrders as $salesOrder) {
                $this->info("  Posting missing entry for Order {$salesOrder->order_number} (Date: {$salesOrder->date->toDateString()})");

                $farmId = $salesOrder->farm_id ?? $salesOrder->harvestOperation?->farm_id;

                try {
                    $this->posting->post('sales.credit', [
                        'amount' => (float) $salesOrder->net_amount,
                        'farm_id' => $farmId,
                        'date' => $salesOrder->date?->toDateString(),
                        'source_type' => $salesOrder->getMorphClass(),
                        'source_id' => $salesOrder->id,
                        'description' => "مبيعات - رقم الأوردر {$salesOrder->order_number}",
                        'user_id' => $salesOrder->created_by,
                        'debit_account_id' => $trader->account_id,
                        'trader_statement_id' => $trader->activeStatement?->id,
                    ]);
                } catch (\Exception $e) {
                    $this->error("  Failed to post for Order {$salesOrder->order_number}: ".$e->getMessage());
                }
            }

            // Part 2: Link orphaned journal entries to a statement session
            if ($this->option('link-orphaned')) {
                $orphanedEntryIds = JournalEntry::whereNull('trader_statement_id')
                    ->whereHas('lines', fn ($q) => $q->where('account_id', $trader->account_id))
                    ->pluck('id');

                if ($orphanedEntryIds->isEmpty()) {
                    continue;
                }

                // Find or create an "Initial Session"
                $statement = $trader->statements()->where('status', TraderStatementStatus::Open)->first();

                if (! $statement) {
                    $statement = $trader->statements()->create([
                        'title' => 'البداية (معالجة تلقائية)',
                        'opened_at' => JournalEntry::whereIn('id', $orphanedEntryIds)->min('date') ?? now()->toDateString(),
                        'opening_balance' => 0,
                        'status' => TraderStatementStatus::Open,
                        'notes' => 'جلسة افتتاحية تم إنشاؤها تلقائياً لربط العمليات السابقة.',
                    ]);
                }

                $this->info("  Linking {$orphanedEntryIds->count()} entries to statement #{$statement->id} ({$statement->title})");

                DB::table('journal_entries')
                    ->whereIn('id', $orphanedEntryIds)
                    ->update(['trader_statement_id' => $statement->id]);
            }
        }

        $this->info('Repair successfully completed!');
    }
}
