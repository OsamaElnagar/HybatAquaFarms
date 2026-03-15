<?php

namespace App\Filament\Resources\Traders\Actions;

use App\Domain\Accounting\PostingService;
use App\Models\Account;
use App\Models\Trader;
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
                    ->default(fn(Trader $record) => max(0, $record->outstanding_balance)),
                Select::make('treasury_account_id')
                    ->label('الخزينة المستلمة')
                    ->options(fn() => Account::where('is_treasury', true)->pluck('name', 'id'))
                    ->required(),
                Textarea::make('description')
                    ->label('البيان')
                    ->default('تحصيل نقدية من تاجر'),
            ])
            ->action(function (array $data, Trader $record, PostingService $posting) {
                $posting->post('voucher.receipt', [
                    'amount' => $data['amount'],
                    'date' => $data['date'],
                    'description' => $data['description'],
                    'source_type' => Trader::class,
                    'source_id' => $record->id,
                    'debit_account_id' => $data['treasury_account_id'],
                    'credit_account_id' => $record->account_id,
                    'trader_statement_id' => $record->activeStatement?->id,
                ]);
            })
            ->slideOver()
            ->successNotificationTitle('تم تسجيل الدفعة بنجاح');
    }
}
