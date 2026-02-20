<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DailyFeedImportsChart;
use BackedEnum;
use Filament\Pages\Page;

use App\Enums\FeedMovementType;
use App\Models\Factory;
use App\Models\FeedItem;
use App\Models\FeedMovement;
use App\Models\FeedWarehouse;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

class DailyFeedImportsReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }

    public static function getNavigationLabel(): string
    {
        return 'واردات الأعلاف اليومية';
    }

    protected static ?string $title = 'تقرير واردات الأعلاف اليومية';

    protected string $view = 'filament.pages.daily-feed-imports-report';

    #[Url]
    public ?array $filters = [
        'date_start' => null,
        'date_end' => null,
        'factory_id' => null,
        'feed_item_id' => null,
        'warehouse_id' => null,
    ];

    public function mount(): void
    {
        if (empty($this->filters['date_start'])) {
            $this->filters['date_start'] = now()->subDays(30)->format('Y-m-d');
        }
        if (empty($this->filters['date_end'])) {
            $this->filters['date_end'] = now()->format('Y-m-d');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DailyFeedImportsChart::class,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('خيارات التقرير')
                    ->schema([
                        DatePicker::make('filters.date_start')
                            ->label('من تاريخ')
                            ->default(now()->subDays(30))
                            ->live(),
                        DatePicker::make('filters.date_end')
                            ->label('إلى تاريخ')
                            ->default(now())
                            ->live(),
                        Select::make('filters.factory_id')
                            ->label('المصنع')
                            ->options(Factory::where('is_active', true)->pluck('name', 'id'))
                            ->placeholder('جميع المصانع')
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('filters.feed_item_id')
                            ->label('صنف العلف')
                            ->options(FeedItem::where('is_active', true)->pluck('name', 'id'))
                            ->placeholder('جميع الأصناف')
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('filters.warehouse_id')
                            ->label('المخزن')
                            ->options(FeedWarehouse::where('is_active', true)->pluck('name', 'id'))
                            ->placeholder('جميع المخازن')
                            ->searchable()
                            ->preload()
                            ->live(),
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = FeedMovement::query()
                    ->where('movement_type', FeedMovementType::In);

                if (!empty($this->filters['date_start'])) {
                    $query->whereDate('date', '>=', $this->filters['date_start']);
                }

                if (!empty($this->filters['date_end'])) {
                    $query->whereDate('date', '<=', $this->filters['date_end']);
                }

                if (!empty($this->filters['factory_id'])) {
                    $query->where('factory_id', $this->filters['factory_id']);
                }

                if (!empty($this->filters['feed_item_id'])) {
                    $query->where('feed_item_id', $this->filters['feed_item_id']);
                }

                if (!empty($this->filters['warehouse_id'])) {
                    $query->where('to_warehouse_id', $this->filters['warehouse_id']);
                }

                return $query->with(['factory', 'feedItem', 'toWarehouse']);
            })
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('factory.name')
                    ->label('المصنع')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('feedItem.name')
                    ->label('الصنف')
                    ->sortable(),
                TextColumn::make('toWarehouse.name')
                    ->label('المخزن الوارد إليه')
                    ->sortable()
                    ->default('-'),
                TextColumn::make('quantity')
                    ->label('الكمية (كجم)')
                    ->numeric(2)
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
                            'تقرير واردات الأعلاف',
                            ['التاريخ', 'المصنع', 'الصنف', 'المخزن', 'الكمية (كجم)'],
                            $table->getQuery()->get()->map(fn($record) => [
                                $record->date instanceof \Carbon\Carbon ? $record->date->format('Y-m-d') : substr($record->date, 0, 10),
                                $record->factory?->name ?? '-',
                                $record->feedItem?->name ?? '-',
                                $record->toWarehouse?->name ?? '-',
                                number_format($record->quantity, 2),
                            ])->toArray()
                        )->stream('feed-imports-report.pdf');
                    }),
            ]);
    }

    public function updatedFilters(): void
    {
        $this->dispatch('updateCharts');
    }
}

