<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Enums\PaymentStatus;
use App\Enums\VoucherType;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Models\PettyCash;
use App\Models\SalesOrder;
use App\Models\Voucher;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSalesOrder extends EditRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('register_payment')
                ->label('تسجيل دفعة')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn (SalesOrder $record) => $record->payment_status !== PaymentStatus::Paid)
                ->form([
                    TextInput::make('amount')
                        ->label('المبلغ')
                        ->default(fn (SalesOrder $record) => $record->net_amount)
                        ->required()
                        ->numeric()
                        ->minValue(1),
                    Select::make('petty_cash_id')
                        ->label('الخزينة / البنك')
                        ->options(PettyCash::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    DatePicker::make('date')
                        ->label('التاريخ')
                        ->default(now())
                        ->required(),
                    TextInput::make('notes')
                        ->label('ملاحظات'),
                ])
                ->action(function (array $data, SalesOrder $record) {
                    Voucher::create([
                        'farm_id' => $record->farm_id,
                        'voucher_type' => VoucherType::Receipt,
                        'date' => $data['date'],
                        'counterparty_type' => 'App\Models\Trader',
                        'counterparty_id' => $record->trader_id,
                        'petty_cash_id' => $data['petty_cash_id'],
                        'amount' => $data['amount'],
                        'description' => "تحصيل مقابل أمر بيع رقم {$record->order_number}",
                        'created_by' => auth()->id(),
                        'notes' => $data['notes'],
                    ]);

                    if ($data['amount'] >= $record->net_amount) {
                        $record->update(['payment_status' => PaymentStatus::Paid]);
                    } else {
                        $record->update(['payment_status' => PaymentStatus::Partial]);
                    }

                    Notification::make()
                        ->title('تم تسجيل الدفعة بنجاح')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
