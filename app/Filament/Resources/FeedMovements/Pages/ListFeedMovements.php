<?php

namespace App\Filament\Resources\FeedMovements\Pages;

use App\Enums\FeedMovementType;
use App\Filament\Resources\FeedMovements\FeedMovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFeedMovements extends ListRecords
{
    protected static string $resource = FeedMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    // tabs
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('الكل'),
        ];

        foreach (FeedMovementType::cases() as $movementType) {
            $tabs[$movementType->value] = Tab::make($movementType->label())->modifyQueryUsing(function (Builder $query) use ($movementType) {
                return $query->where('movement_type', $movementType->value);
            });
        }

        return $tabs;
    }
}
