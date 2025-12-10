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

class HarvestAndSalesAnalysisReport extends Page implements HasForms
{
    use InteractsWithForms;

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
                                if (! $farmId) {
                                    return [];
                                }

                                return \App\Models\Batch::where('farm_id', $farmId)
                                    ->pluck('batch_code', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('كل الدورات')
                            ->visible(fn (callable $get) => filled($get('filters.farm_id')))
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

    public function updatedFilters(): void
    {
        $this->dispatch('updateCharts');
    }
}
