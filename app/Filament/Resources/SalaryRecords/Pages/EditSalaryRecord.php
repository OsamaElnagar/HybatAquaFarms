<?php

namespace App\Filament\Resources\SalaryRecords\Pages;

use App\Filament\Resources\SalaryRecords\SalaryRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalaryRecord extends EditRecord
{
    protected static string $resource = SalaryRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
