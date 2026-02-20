<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HarvestAndSalesStats;
use App\Filament\Widgets\HarvestByFarmChart;
use App\Filament\Widgets\SalesByCustomerChart;
use App\Filament\Widgets\SalesTrendChart;
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

use App\Models\SalesOrder;
use Filament\Actions\Action as PageAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HarvestAndSalesAnalysisReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static UnitEnum|string|null $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تحليل الحصاد والمبيعات';

    protected static ?string $title = 'تقرير تحليل الحصاد والمبيعات';

    protected string $view = 'filament.pages.harvest-and-sales-analysis-report';

    protected function getHeaderWidgets(): array
    {
        return [
            HarvestAndSalesStats::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            SalesTrendChart::class,
            HarvestByFarmChart::class,
            SalesByCustomerChart::class,
        ];
    }

    #[Url]
    public ?array $filters = [
        'farm_id' => null,
        'batch_id' => null,
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
                            ->placeholder('كل المزارع')
                            ->live(),
                        Select::make('filters.batch_id')
                            ->label('الدورة (اختياري)')
                            ->options(function (callable $get) {
                                $farmId = $get('filters.farm_id');
                                if (!$farmId) {
                                    return [];
                                }

                                return \App\Models\Batch::where('farm_id', $farmId)
                                    ->pluck('batch_code', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('كل الدورات')
                            ->visible(fn(callable $get) => filled($get('filters.farm_id')))
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
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = SalesOrder::query();

                if (!empty($this->filters['farm_id'])) {
                    $query->whereHas('harvestOperation.batch.farm', function ($q) {
                        $q->where('id', $this->filters['farm_id']);
                    });
                }

                if (!empty($this->filters['batch_id'])) {
                    $query->whereHas('harvestOperation', function ($q) {
                        $q->where('batch_id', $this->filters['batch_id']);
                    });
                }

                if (!empty($this->filters['date_start'])) {
                    $query->whereDate('date', '>=', $this->filters['date_start']);
                }

                if (!empty($this->filters['date_end'])) {
                    $query->whereDate('date', '<=', $this->filters['date_end']);
                }

                return $query->with(['trader', 'harvestOperation.batch.farm']);
            })
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('harvestOperation.batch.farm.name')
                    ->label('المزرعة')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('net_amount')
                    ->label('صافي المبلغ')
                    ->money('EGP')
                    ->sortable()
                    ->summarize(Sum::make()->label('الإجمالي')->money('EGP')),
            ])
            ->defaultSort('date', 'desc')
            ->headerActions([
                Action::make('export_pdf')
                    ->label('PDF تصدير')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Table $table) {
                        return app(\App\Services\PdfService::class)->generateReportPdf(
                            'تقرير المبيعات',
                            ['التاريخ', 'رقم الطلب', 'التاجر', 'المزرعة', 'صافي المبلغ'],
                            $table->getQuery()->get()->map(fn($record) => [
                                $record->date->format('Y-m-d'),
                                $record->order_number,
                                $record->trader?->name ?? '-',
                                $record->harvestOperation?->batch?->farm?->name ?? '-',
                                number_format($record->net_amount, 2),
                            ])->toArray()
                        )->stream('sales-report.pdf');
                    }),
            ]);
    }

    public function updatedFilters(): void
    {
        $this->dispatch('updateCharts');
        // Table refreshes automatically via Livewire property binding
    }
}
