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

class MakePaymentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'makePayment';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('صرف مبلغ')
            ->icon('heroicon-o-banknotes')
            ->color('danger')
            ->form([
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->default(now())
                    ->required(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->default(fn (Factory $record) => max(0, $record->outstanding_balance)),
                Select::make('treasury_account_id')
                    ->label('الخزنة الصارفة')
                    ->options(fn () => Account::where('is_treasury', true)->pluck('name', 'id'))
                    ->required(),
                Textarea::make('description')
                    ->label('البيان')
                    ->default('صرف مبلغ لمصنع/مفرخ/مورد'),
            ])
            ->action(function (array $data, Factory $record, PostingService $posting) {
                $posting->post('factory.payment', [
                    'amount' => $data['amount'],
                    'date' => $data['date'],
                    'description' => $data['description'],
                    'source_type' => Factory::class,
                    'source_id' => $record->id,
                    'debit_account_id' => $record->account_id,
                    'credit_account_id' => $data['treasury_account_id'],
                    'factory_statement_id' => $record->activeStatement?->id,
                ]);
            })
            ->slideOver()
            ->successNotificationTitle('تم تسجيل المبلغ بنجاح');
    }
}
