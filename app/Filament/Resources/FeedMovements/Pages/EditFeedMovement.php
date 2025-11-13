<?php

namespace App\Filament\Resources\FeedMovements\Pages;

use App\Enums\FeedMovementType;
use App\Filament\Resources\FeedMovements\FeedMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditFeedMovement extends EditRecord
{
    protected static string $resource = FeedMovementResource::class;

    public function getTitle(): string|Htmlable
    {
        if ($this->record->movement_type === FeedMovementType::Out) {
            return 'عرض حركة الصرف (غير قابلة للتعديل)';
        }

        return parent::getTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => $this->record->movement_type !== FeedMovementType::Out),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Prevent editing Out movements
        if ($this->record->movement_type === FeedMovementType::Out) {
            $this->form->disabled();
        }

        return $data;
    }
}
