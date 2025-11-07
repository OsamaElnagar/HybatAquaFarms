<?php

namespace App\Filament\Resources\AdvanceRepayments\Pages;

use App\Filament\Resources\AdvanceRepayments\AdvanceRepaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdvanceRepayment extends EditRecord
{
    protected static string $resource = AdvanceRepaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
