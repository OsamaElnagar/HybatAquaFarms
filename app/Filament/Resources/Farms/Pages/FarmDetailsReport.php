<?php

namespace App\Filament\Resources\Farms\Pages;

use App\Filament\Resources\Farms\FarmResource;
use App\Models\Batch;
use App\Models\DailyFeedIssue;
use App\Models\FarmExpense;
use App\Models\PettyCashTransaction;
use App\Models\SalesOrder;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class FarmDetailsReport extends Page
{
    use InteractsWithRecord;

    protected static string $resource = FarmResource::class;

    protected string $view = 'filament.resources.farms.pages.farm-details-report';

    protected static ?string $title = 'تقرير تفاصيل المزرعة';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'تقرير تفاصيل المزرعة: '.$this->record->name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            '#' => static::$title,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('العودة للمزرعة')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->url(fn () => FarmResource::getUrl('view', ['record' => $this->record])),
        ];
    }

    public function getSummaryStats(): array
    {
        $farmId = $this->record->id;

        $pettyQuery = PettyCashTransaction::where('farm_id', $farmId)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo));

        $expenseQuery = FarmExpense::where('farm_id', $farmId)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo));

        $salesQuery = SalesOrder::whereHas('harvestOperation', fn ($q) => $q->where('farm_id', $farmId))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo));

        $batchQuery = Batch::where('farm_id', $farmId)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('entry_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('entry_date', '<=', $this->dateTo));

        $feedQuery = DailyFeedIssue::where('farm_id', $farmId)
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo));

        $pettyCashIn = (clone $pettyQuery)->where('direction', 'in')->sum('amount');

        $farmExpenses = (clone $expenseQuery)->where('type', 'expense')->sum('amount');
        $farmRevenue = (clone $expenseQuery)->where('type', 'revenue')->sum('amount');

        $totalSales = (clone $salesQuery)->sum('net_amount');

        $batchCount = (clone $batchQuery)->count();
        $batchHatcheryCost = (clone $batchQuery)->sum('total_cost');

        $totalFeedQuantity = (clone $feedQuery)->sum('quantity');
        $totalFeedCost = (clone $feedQuery)
            ->join('feed_items', 'daily_feed_issues.feed_item_id', '=', 'feed_items.id')
            ->sum(\DB::raw('daily_feed_issues.quantity * COALESCE(feed_items.standard_cost, 0)'));

        $totalExpenses = $pettyCashIn + $farmExpenses + $batchHatcheryCost + $totalFeedCost;
        $totalRevenue = $farmRevenue + $totalSales;

        return [
            'petty_cash_in' => $pettyCashIn,
            'farm_expenses' => $farmExpenses,
            'farm_revenue' => $farmRevenue,
            'total_sales' => $totalSales,
            'batch_count' => $batchCount,
            'batch_hatchery_cost' => $batchHatcheryCost,
            'total_feed_quantity' => $totalFeedQuantity,
            'total_feed_cost' => $totalFeedCost,
            'total_expenses' => $totalExpenses,
            'total_revenue' => $totalRevenue,
            'net_profit' => $totalRevenue - $totalExpenses,
        ];
    }
}
