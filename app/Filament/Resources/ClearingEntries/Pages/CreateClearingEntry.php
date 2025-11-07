<?php

namespace App\Filament\Resources\ClearingEntries\Pages;

use App\Filament\Resources\ClearingEntries\ClearingEntryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateClearingEntry extends CreateRecord
{
    protected static string $resource = ClearingEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }
}
