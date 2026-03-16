<?php

namespace App\Filament\Resources\ExternalCalculations\Pages;

use App\Filament\Resources\ExternalCalculations\Actions\OpenNewStatementAction;
use App\Filament\Resources\ExternalCalculations\Actions\RecordEntryAction;
use App\Filament\Resources\ExternalCalculations\ExternalCalculationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalCalculation extends EditRecord
{
    protected static string $resource = ExternalCalculationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            OpenNewStatementAction::make(),
            RecordEntryAction::make(),
        ];
    }
}
