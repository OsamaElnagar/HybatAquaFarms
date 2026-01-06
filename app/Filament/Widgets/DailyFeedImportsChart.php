<?php

namespace App\Filament\Widgets;

use App\Enums\FeedMovementType;
use App\Models\Factory;
use App\Models\FeedItem;
use App\Models\FeedMovement;
use App\Models\FeedWarehouse;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class DailyFeedImportsChart extends ApexChartWidget
{
    use HasFiltersSchema;

    protected static ?string $chartId = 'dailyFeedImportsChart';

    protected static ?string $heading = 'واردات الأعلاف اليومية';

    protected static ?string $subheading = 'رسم بياني لكميات الأعلاف الواردة يومياً';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $contentHeight = 350;

    protected ?string $pollingInterval = null;

    public function filtersSchema(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date_start')
                ->label('من تاريخ')
                ->default(now()->subDays(30))
                ->live(),
            DatePicker::make('date_end')
                ->label('إلى تاريخ')
                ->default(now())
                ->live(),
            Select::make('factory_id')
                ->label('المصنع')
                ->options(Factory::where('is_active', true)->pluck('name', 'id'))
                ->placeholder('جميع المصانع')
                ->live(),
            Select::make('feed_item_id')
                ->label('صنف العلف')
                ->options(FeedItem::where('is_active', true)->pluck('name', 'id'))
                ->placeholder('جميع الأصناف')
                ->live(),
            Select::make('warehouse_id')
                ->label('المخزن')
                ->options(FeedWarehouse::where('is_active', true)->pluck('name', 'id'))
                ->placeholder('جميع المخازن')
                ->live(),
        ]);
    }

    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    protected function getOptions(): array
    {
        $dateStart = Carbon::parse($this->filters['date_start'] ?? now()->subDays(30));
        $dateEnd = Carbon::parse($this->filters['date_end'] ?? now());
        $factoryId = $this->filters['factory_id'] ?? null;
        $feedItemId = $this->filters['feed_item_id'] ?? null;
        $warehouseId = $this->filters['warehouse_id'] ?? null;

        $query = FeedMovement::query()
            ->where('movement_type', FeedMovementType::In)
            ->whereBetween('date', [$dateStart->startOfDay(), $dateEnd->endOfDay()]);

        if ($factoryId) {
            $query->where('factory_id', $factoryId);
        }

        if ($feedItemId) {
            $query->where('feed_item_id', $feedItemId);
        }

        if ($warehouseId) {
            $query->where('to_warehouse_id', $warehouseId);
        }

        $data = $query
            ->selectRaw('date, SUM(quantity) as total_quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zero values
        $period = $dateStart->daysUntil($dateEnd);
        $categories = [];
        $values = [];
        $dataByDate = $data->mapWithKeys(function ($item) {
            $dateStr = $item->date instanceof Carbon
                ? $item->date->format('Y-m-d')
                : substr((string) $item->date, 0, 10);

            return [$dateStr => $item];
        });

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $categories[] = $date->format('m/d');
            $values[] = (float) ($dataByDate[$dateStr]->total_quantity ?? 0);
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'الكمية (كجم)',
                    'data' => $values,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#3b82f6'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'light',
                    'type' => 'vertical',
                    'shadeIntensity' => 0.5,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.3,
                ],
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => "function (val) { return val.toFixed(2) + ' كجم'; }",
                ],
            ],
        ];
    }
}
