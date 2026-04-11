<?php

namespace App\Console\Commands;

use App\Models\FarmReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ReportsCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup {--days=30 : The number of days to keep reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old farm statistics reports and their associated PDF files';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning up reports older than {$days} days (before {$cutoffDate->toDateString()})...");

        $oldReports = FarmReport::where('created_at', '<', $cutoffDate)->get();

        $count = $oldReports->count();

        if ($count === 0) {
            $this->info('No old reports found to clean up.');

            return;
        }

        $deletedFiles = 0;

        foreach ($oldReports as $report) {
            if ($report->pdf_path && Storage::disk('public')->exists($report->pdf_path)) {
                Storage::disk('public')->delete($report->pdf_path);
                $deletedFiles++;
            }

            $report->delete();
        }

        $this->info("Successfully deleted {$count} report records and {$deletedFiles} PDF files.");
    }
}
