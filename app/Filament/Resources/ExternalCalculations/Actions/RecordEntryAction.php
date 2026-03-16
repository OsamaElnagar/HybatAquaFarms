<?php

namespace App\Filament\Resources\ExternalCalculations\Actions;

use App\Enums\AccountType;
use App\Enums\ExternalCalculationType;
use App\Models\Account;
use App\Models\ExternalCalculation;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;

class RecordEntryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'recordEntry';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('تسجيل معاملة')
            ->icon('heroicon-o-plus-circle')
            ->color('success')
            ->modalHeading('تسجيل معاملة مالية جديدة')
            ->modalWidth('2xl')
            ->form([
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->default(now())
                    ->required()
                    ->native(false),
                Select::make('type')
                    ->label('النوع')
                    ->options(ExternalCalculationType::class)
                    ->required()
                    ->live(),
                Select::make('treasury_account_id')
                    ->label('الخزينة الصادر منها')
                    ->options(fn() => Account::where('is_treasury', true)->pluck('name', 'id'))
                    ->required(),
                Select::make('account_id')
                    ->label('الحساب المقابل')
                    ->options(function (Get $get) {
                        $type = $get('type');
                        $query = Account::query();

                        if ($type === ExternalCalculationType::Payment) {
                            $query->where('type', AccountType::Expense);
                        } elseif ($type === ExternalCalculationType::Receipt) {
                            $query->where('type', AccountType::Income);
                        } else {
                            return [];
                        }

                        return $query->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                TextInput::make('reference_number')
                    ->label('رقم المرجع'),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                Hidden::make('created_by')
                    ->default(auth()->id()),
            ])
            ->action(function (array $data, ExternalCalculation $record): void {
                $activeStatement = $record->activeStatement;

                if (!$activeStatement) {
                    Notification::make()
                        ->title('لا يوجد كشف حساب مفتوح')
                        ->body('يرجى فتح كشف حساب جديد أولاً.')
                        ->danger()
                        ->send();

                    return;
                }

                $record->entries()->create(array_merge($data, [
                    'external_calculation_statement_id' => $activeStatement->id,
                ]));

                Notification::make()
                    ->title('تم تسجيل المعاملة بنجاح')
                    ->success()
                    ->send();
            })
            ->slideOver();
    }
}
