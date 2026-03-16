<?php

namespace App\Filament\Resources\ExternalCalculations\Pages;

use App\Filament\Resources\ExternalCalculations\Actions\OpenNewStatementAction;
use App\Filament\Resources\ExternalCalculations\Actions\RecordEntryAction;
use App\Filament\Resources\ExternalCalculations\ExternalCalculationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExternalCalculation extends ViewRecord
{
    protected static string $resource = ExternalCalculationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            OpenNewStatementAction::make(),
            RecordEntryAction::make(),
        ];
    }
}
