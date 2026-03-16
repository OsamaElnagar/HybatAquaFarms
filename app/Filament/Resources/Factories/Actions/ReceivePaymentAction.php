<?php

namespace App\Filament\Resources\Factories\Actions;

use App\Domain\Accounting\PostingService;
use App\Models\Account;
use App\Models\Factory;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ReceivePaymentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'receivePayment';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('استلام مبلغ')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->form([
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->default(now())
                    ->required(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->default(0),
                Select::make('treasury_account_id')
                    ->label('الخزينة المستلمة')
                    ->options(fn () => Account::where('is_treasury', true)->pluck('name', 'id'))
                    ->required(),
                Textarea::make('description')
                    ->label('البيان')
                    ->default('استلام نقدية من مصنع/مفرخ/مورد'),
            ])
            ->action(function (array $data, Factory $record, PostingService $posting) {
                $posting->post('factory.receipt', [
                    'amount' => $data['amount'],
                    'date' => $data['date'],
                    'description' => $data['description'],
                    'source_type' => Factory::class,
                    'source_id' => $record->id,
                    'debit_account_id' => $data['treasury_account_id'],
                    'credit_account_id' => $record->account_id,
                    'factory_statement_id' => $record->activeStatement?->id,
                ]);
            })
            ->slideOver()
            ->successNotificationTitle('تم تسجيل المبلغ بنجاح');
    }
}
