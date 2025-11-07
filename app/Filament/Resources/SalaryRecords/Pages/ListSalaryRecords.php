<?php

namespace App\Filament\Resources\SalaryRecords\Pages;

use App\Filament\Resources\SalaryRecords\SalaryRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalaryRecords extends ListRecords
{
    protected static string $resource = SalaryRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
