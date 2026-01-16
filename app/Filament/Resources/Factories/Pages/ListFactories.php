<?php

namespace App\Filament\Resources\Factories\Pages;

use App\Enums\FactoryType;
use App\Filament\Resources\Factories\FactoryResource;
use App\Filament\Resources\Factories\Widgets\FactoriesStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFactories extends ListRecords
{
    protected static string $resource = FactoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FactoriesStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('الكل')->icon('heroicon-m-rectangle-stack'),
        ];

        foreach (FactoryType::cases() as $faType) {
            $tabs[$faType->value] = Tab::make($faType->getLabel())->modifyQueryUsing(function (Builder $query) use ($faType) {
                return $query->where('type', $faType->value);
            })->icon($faType->getIcon());
        }

        return $tabs;
    }
}
