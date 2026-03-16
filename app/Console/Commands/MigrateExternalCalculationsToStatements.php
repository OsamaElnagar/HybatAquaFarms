<?php

namespace App\Console\Commands;

use App\Enums\ExternalCalculationStatementStatus;
use App\Models\ExternalCalculation;
use App\Models\JournalEntry;
use Illuminate\Console\Command;

class MigrateExternalCalculationsToStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-external-calculations-to-statements';

    protected $description = 'Migrate existing external calculation entries and journal entries into new statement sessions.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting Data Migration for External Calculation Statements...');

        $calculations = ExternalCalculation::all();

        foreach ($calculations as $calc) {
            $this->comment("Processing Calculation: {$calc->name} (ID: {$calc->id})");

            $statement = $calc->activeStatement;

            if (! $statement) {
                $this->info("Creating initial statement for ID: {$calc->id}");
                $statement = $calc->statements()->create([
                    'title' => 'دورة افتتاحية (هجرة بيانات)',
                    'opened_at' => $calc->created_at,
                    'status' => ExternalCalculationStatementStatus::Open,
                    'notes' => 'تم إنشاؤه تلقائياً أثناء هجرة البيانات',
                ]);
            }

            $unlinkedEntriesCount = $calc->entries()->whereNull('external_calculation_statement_id')->count();

            if ($unlinkedEntriesCount > 0) {
                $this->info("Linking {$unlinkedEntriesCount} unlinked entries.");

                $calc->entries()->whereNull('external_calculation_statement_id')->update([
                    'external_calculation_statement_id' => $statement->id,
                ]);

                $entryIds = $calc->entries()
                    ->where('external_calculation_statement_id', $statement->id)
                    ->pluck('journal_entry_id')
                    ->filter();

                if ($entryIds->isNotEmpty()) {
                    JournalEntry::whereIn('id', $entryIds)
                        ->whereNull('external_calculation_statement_id')
                        ->update(['external_calculation_statement_id' => $statement->id]);
                    $this->info('Linked journal entries for these transactions.');
                }
            } else {
                $this->info('No unlinked entries found.');
            }
        }

        $this->info('Data Migration Completed Successfully.');
    }
}
