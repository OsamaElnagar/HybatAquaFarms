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

class GiveCashAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'giveCash';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('صرف/تسليم مبلغ')
            ->icon('heroicon-o-currency-dollar')
            ->color('warning')
            ->form([
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->default(now())
                    ->required(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required(),
                Select::make('treasury_account_id')
                    ->label('الخزينة الصادر منها')
                    ->options(fn () => Account::where('is_treasury', true)->pluck('name', 'id'))
                    ->required(),
                Textarea::make('description')
                    ->label('البيان')
                    ->default('صرف نقدية لتاجر'),
            ])
            ->action(function (array $data, Trader $record, PostingService $posting) {
                $posting->post('voucher.payment', [
                    'amount' => $data['amount'],
                    'date' => $data['date'],
                    'description' => $data['description'],
                    'source_type' => Trader::class,
                    'source_id' => $record->id,
                    'debit_account_id' => $record->account_id,
                    'credit_account_id' => $data['treasury_account_id'],
                    'trader_statement_id' => $record->activeStatement?->id,
                ]);
            })
            ->slideOver()
            ->successNotificationTitle('تم تسجيل الصرف بنجاح');
    }
}
