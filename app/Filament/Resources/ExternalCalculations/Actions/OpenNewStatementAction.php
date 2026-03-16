<?php

namespace App\Filament\Resources\ExternalCalculations\Actions;

use App\Models\ExternalCalculation;
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

        $this->label('إغلاق وفتح كشف حساب جديد')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('إغلاق الكشف الحالي وفتح كشف جديد')
            ->modalDescription('سيتم إغلاق الكشف الحالي بالرصيد الختامي الحالي، وفتح كشف جديد يبدأ من هذا الرصيد كرصيد افتتاحي.')
            ->form([
                TextInput::make('title')
                    ->label('اسم الكشف')
                    ->placeholder('مثال: الموسم الأول 2026')
                    ->required(),
                TextInput::make('opening_balance')
                    ->label('رصيد افتتاحي مخصص (اختياري)')
                    ->numeric()
                    ->helperText('اتركه فارغاً ليتم سحب الرصيد الختامي من الكشف السابق تلقائياً.'),
                Textarea::make('notes')->label('ملاحظات (اختياري)'),
            ])
            ->action(function (array $data, ExternalCalculation $record) {
                $record->openNewStatement(
                    title: $data['title'],
                    notes: $data['notes'] ?? null,
                    openingBalance: $data['opening_balance'] !== '' ? (float) $data['opening_balance'] : null
                );

                Notification::make()
                    ->title('تم فتح كشف حساب جديد')
                    ->success()
                    ->send();
            })->slideOver();
    }
}
