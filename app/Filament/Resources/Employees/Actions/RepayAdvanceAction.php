<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Enums\PaymentMethod;
use App\Models\Account;
use App\Models\AdvanceRepayment;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class RepayAdvanceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'repayAdvance';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('سداد سُلفة')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->form([
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->default(now())
                    ->required(),
                TextInput::make('amount')
                    ->label('المبلغ المدفوع')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->maxValue(fn (Employee $record) => $record->advances()->where('balance_remaining', '>', 0)->sum('balance_remaining'))
                    ->helperText(fn (Employee $record) => 'إجمالي السلف المتبقية: '.number_format($record->advances()->where('balance_remaining', '>', 0)->sum('balance_remaining'), 2).' EGP'),
                Select::make('payment_method')
                    ->label('طريقة السداد')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::CASH)
                    ->required(),
                Select::make('treasury_account_id')
                    ->label('الخزينة المستلمة')
                    ->options(fn () => Account::where('is_treasury', true)->pluck('name', 'id'))
                    ->default(fn () => Account::where('code', '1120')->value('id'))
                    ->visible(fn ($get) => $get('payment_method') === PaymentMethod::CASH->value)
                    ->required(fn ($get) => $get('payment_method') === PaymentMethod::CASH->value),
                Textarea::make('notes')
                    ->label('الملاحظات')
                    ->default('سداد قسط سُلفة'),
            ])
            ->action(function (array $data, Employee $record) {
                $amountToPay = (float) $data['amount'];

                $advances = EmployeeAdvance::where('employee_id', $record->id)
                    ->where('balance_remaining', '>', 0)
                    ->orderBy('approved_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($advances as $advance) {
                    if ($amountToPay <= 0) {
                        break;
                    }

                    $paidForThisAdvance = min((float) $advance->balance_remaining, $amountToPay);

                    AdvanceRepayment::create([
                        'employee_advance_id' => $advance->id,
                        'amount_paid' => $paidForThisAdvance,
                        'payment_date' => $data['date'],
                        'payment_method' => $data['payment_method'],
                        'notes' => count($advances) > 1 ? ($data['notes'].' (سداد مجمع)') : $data['notes'],
                        'balance_remaining' => max(0, $advance->balance_remaining - $paidForThisAdvance),
                    ]);

                    $amountToPay -= $paidForThisAdvance;
                }

                Notification::make()
                    ->title('تم تسجيل السداد بنجاح')
                    ->success()
                    ->send();
            })
            ->slideOver();
    }
}
