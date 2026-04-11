<?php

namespace App\Jobs;

use App\Filament\Resources\Farms\FarmResource;
use App\Models\FarmReport;
use App\Services\PdfService;
use App\Services\Reports\FarmStatsService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateFarmStatsReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $reportId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        FarmStatsService $statsService,
        PdfService $pdfService
    ): void {
        $report = FarmReport::find($this->reportId);

        if (! $report) {
            return;
        }

        try {
            $report->update(['status' => 'processing']);

            $farm = $report->farm;
            $batches = $statsService->getBatches($farm, $report->filters);
            $farmExpenses = $statsService->getFarmExpenses($farm, $report->filters);
            $stats = $statsService->calculateStats($batches, $farmExpenses);

            $pdf = $pdfService->generateFarmStatsPdf(
                $farm->name,
                $stats,
                $report->filters
            );

            $filename = 'reports/farm-stats-'.$report->id.'-'.time().'.pdf';
            Storage::disk('public')->put($filename, $pdf->output());

            $report->update([
                'total_expenses' => $stats['total_expenses'],
                'total_revenue' => $stats['total_revenue'],
                'net_profit' => $stats['net_profit'],
                'profit_margin' => $stats['profit_margin'],
                'batch_count' => $stats['batch_count'],
                'extra_expenses' => $stats['extra_expenses'],
                'extra_revenue' => $stats['extra_revenue'],
                'other_transactions' => $stats['other_transactions'],
                'pdf_path' => $filename,
                'status' => 'completed',
            ]);

            $this->sendNotification($report);
        } catch (\Exception $e) {
            $report->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Farm Stats Report Generation Failed: '.$e->getMessage());
        }
    }

    protected function sendNotification(FarmReport $report): void
    {
        $user = $report->user;

        if (! $user) {
            return;
        }

        Notification::make()
            ->success()
            ->title('تم إنشاء تقرير إحصائيات المزرعة')
            ->body('تقرير المزرعة: '.$report->farm->name.' جاهز الآن للعرض.')
            ->actions([
                Action::make('view')
                    ->label('عرض النتائج')
                    ->url(FarmResource::getUrl('stats-results', ['record' => $report->farm, 'report' => $report->id])),
                Action::make('download')
                    ->label('تحميل PDF')
                    ->url(Storage::disk('public')->url($report->pdf_path))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase($user);
    }
}
