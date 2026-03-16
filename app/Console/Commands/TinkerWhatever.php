<?php

namespace App\Console\Commands;

use App\Models\ExternalCalculationEntry;
use App\Models\JournalEntry;
use Illuminate\Console\Command;

class TinkerWhatever extends Command
{
    protected $signature = 'app:tinker-whatever';

    protected $description = ' Instead of running artisan tanker execute commands that doesn\'t work let\'s add whatever we want over here, And update whenever needed';

    public function handle()
    {
        $this->info('Starting Post-Migration Verification...');

        $totalEntries = ExternalCalculationEntry::count();
        $linkedEntries = ExternalCalculationEntry::whereNotNull('external_calculation_statement_id')->count();
        $unlinkedEntries = ExternalCalculationEntry::whereNull('external_calculation_statement_id')->count();

        $this->info("Total Entries: {$totalEntries}");
        $this->info("Linked Entries: {$linkedEntries}");
        $this->info("Unlinked Entries: {$unlinkedEntries}");

        if ($unlinkedEntries === 0) {
            $this->info('✓ SUCCESS: All entries are linked to a statement.');
        } else {
            $this->error('✗ FAILURE: Some entries are still unlinked.');
        }

        $totalJournalEntries = JournalEntry::whereNotNull('external_calculation_statement_id')->count();
        $this->info("Linked Journal Entries: {$totalJournalEntries}");

        $this->info('Verification Completed.');
    }
}
