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

class FarmFeedConsumptionReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static UnitEnum|string|null $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير استهلاك المزرعة';

    protected static ?string $title = 'تقرير استهلاك الأعلاف للمزرعة';

    protected string $view = 'filament.pages.farm-feed-consumption-report';

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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('خيارات التقرير')
                    ->schema([
                        Select::make('filters.farm_id')
                            ->label('المزرعة')
                            ->options(Farm::where('status', 'active')->pluck('name', 'id')) // Assuming active farms
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

    public function updatedFilters(): void
    {
        $this->dispatch('updateCharts');
    }
}
