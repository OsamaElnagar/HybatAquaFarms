<?php

namespace App\Filament\Resources\Factories\Actions;

use App\Models\Factory;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class OpenNewStatementAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'openNewStatement';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('إغلاق وفتح كشف جديد')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('إغلاق الكشف الحالي وفتح كشف جديد')
            ->modalDescription('سيتم إغلاق الكشف الحالي بالرصيد الختامي الحالي، وسيُفتح كشف جديد يبدأ من هذا الرصيد كرصيد افتتاحي.')
            ->form([
                TextInput::make('title')
                    ->label('عنوان الكشف (اختياري)')
                    ->placeholder('مثال: دورة الربع الأول 2026'),
                Textarea::make('notes')->label('ملاحظات (اختياري)'),
            ])
            ->action(function (array $data, Factory $record, $livewire) {
                $newStatement = $record->openNewStatement(
                    title: $data['title'] ?? null,
                    notes: $data['notes'] ?? null
                );

                if (method_exists($livewire, 'setActiveStatementId')) {
                    $livewire->setActiveStatementId($newStatement->id);
                }

                Notification::make()
                    ->title('تم فتح كشف حساب جديد')
                    ->success()
                    ->send();
            })->slideOver();
    }
}
