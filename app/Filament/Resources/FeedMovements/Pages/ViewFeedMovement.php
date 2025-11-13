<?php

namespace App\Filament\Resources\FeedMovements\Pages;

use App\Filament\Resources\FeedMovements\FeedMovementResource;
use App\Filament\Resources\FeedMovements\Infolists\FeedMovementInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewFeedMovement extends ViewRecord
{
    protected static string $resource = FeedMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn ($record) => $record->movement_type !== \App\Enums\FeedMovementType::Out),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return FeedMovementInfolist::configure($schema);
    }
}
