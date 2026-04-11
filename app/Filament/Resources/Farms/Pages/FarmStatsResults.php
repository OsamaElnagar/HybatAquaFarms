<?php

namespace App\Filament\Resources\Farms\Pages;

use App\Filament\Resources\Farms\FarmResource;
use App\Models\FarmReport;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

class FarmStatsResults extends Page
{
    use InteractsWithRecord;

    protected static string $resource = FarmResource::class;

    protected string $view = 'filament.resources.farms.pages.farm-stats-results';

    public ?int $reportId = null;

    public ?FarmReport $report = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->reportId = request()->query('report');

        if ($this->reportId) {
            $this->report = FarmReport::find($this->reportId);
        }

        if (! $this->report || $this->report->farm_id !== $this->record->id) {
            // Alternatively, fetch the latest completed report for this farm
            $this->report = FarmReport::where('farm_id', $this->record->id)
                ->where('status', 'completed')
                ->latest()
                ->first();
        }
    }

    public function getTitle(): string
    {
        return 'نتائج إحصائيات المزرعة: '.$this->record->name;
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('تحميل PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn () => $this->report?->pdf_path
                    ? Storage::disk('public')->url($this->report->pdf_path)
                    : null)
                ->openUrlInNewTab()
                ->visible(fn () => $this->report && $this->report->status === 'completed'),

            Action::make('back')
                ->label('العودة للمزرعة')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->url(fn () => FarmResource::getUrl('view', ['record' => $this->record])),
        ];
    }
}
