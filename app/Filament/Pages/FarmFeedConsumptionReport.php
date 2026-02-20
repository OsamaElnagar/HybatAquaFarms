<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FarmDailyFeedConsumptionChart;
use App\Filament\Widgets\FarmFeedConsumptionStats;
use App\Filament\Widgets\FarmFeedTypeConsumptionChart;
use App\Filament\Widgets\FarmUnitConsumptionChart;
use App\Models\Farm;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;
use UnitEnum;
use App\Models\DailyFeedIssue;
use Filament\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FarmFeedConsumptionReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static UnitEnum|string|null $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير استهلاك المزرعة';

    protected static ?string $title = 'تقرير استهلاك الأعلاف للمزرعة';

    protected string $view = 'filament.pages.farm-feed-consumption-report';

    #[Url]
    public ?array $filters = [
        'farm_id' => null,
        'date_start' => null,
        'date_end' => null,
    ];

    public function mount(): void
    {
        if (empty($this->filters['date_start'])) {
            $this->filters['date_start'] = now()->startOfMonth()->format('Y-m-d');
        }
        if (empty($this->filters['date_end'])) {
            $this->filters['date_end'] = now()->format('Y-m-d');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FarmFeedConsumptionStats::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            FarmDailyFeedConsumptionChart::class,
            FarmFeedTypeConsumptionChart::class,
            FarmUnitConsumptionChart::class,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('خيارات التقرير')
                    ->schema([
                        Select::make('filters.farm_id')
                            ->label('المزرعة')
                            ->options(Farm::where('status', 'active')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('اختر المزرعة')
                            ->live(),
                        DatePicker::make('filters.date_start')
                            ->label('من تاريخ')
                            ->default(now()->startOfMonth())
                            ->live(),
                        DatePicker::make('filters.date_end')
                            ->label('إلى تاريخ')
                            ->default(now())
                            ->live(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = DailyFeedIssue::query();

                if (!empty($this->filters['farm_id'])) {
                    $query->where('farm_id', $this->filters['farm_id']);
                }

                if (!empty($this->filters['date_start'])) {
                    $query->whereDate('date', '>=', $this->filters['date_start']);
                }

                if (!empty($this->filters['date_end'])) {
                    $query->whereDate('date', '<=', $this->filters['date_end']);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable(),
                TextColumn::make('batch.batch_code')
                    ->label('الدورة')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('feedItem.name')
                    ->label('نوع العلف')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية (كجم)')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('الإجمالي')),
            ])
            ->defaultSort('date', 'desc')
            ->headerActions([
                Action::make('export_pdf')
                    ->label('PDF تصدير')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Table $table) {
                        return app(\App\Services\PdfService::class)->generateReportPdf(
                            'تقرير استهلاك الأعلاف',
                            ['التاريخ', 'المزرعة', 'الدورة', 'نوع العلف', 'الكمية (كجم)'],
                            $table->getQuery()->get()->map(fn($record) => [
                                $record->date->format('Y-m-d H:i A'),
                                $record->farm?->name ?? '-',
                                $record->batch?->batch_code ?? '-',
                                $record->feedItem?->name ?? '-',
                                number_format($record->quantity),
                            ])->toArray()
                        )->stream('feed-consumption-report.pdf');
                    }),
            ]);
    }

    public function updatedFilters(): void
    {
        $this->dispatch('updateCharts');
        // Refresh table if needed, usually automatic due to livewire property binding
    }
}

