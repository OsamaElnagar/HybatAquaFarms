<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Models\Account;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class GiveAdvanceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'giveAdvance';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('صرف سُلفة')
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
                    ->default(fn () => Account::where('code', '1120')->value('id'))
                    ->required(),
                Textarea::make('reason')
                    ->label('السبب / البيان')
                    ->default('سلفة موظف'),
            ])
            ->action(function (array $data, Employee $record) {
                EmployeeAdvance::create([
                    'employee_id' => $record->id,
                    'amount' => $data['amount'],
                    'balance_remaining' => $data['amount'],
                    'request_date' => $data['date'],
                    'disbursement_date' => $data['date'],
                    'approved_date' => $data['date'],
                    'reason' => $data['reason'],
                    'approval_status' => AdvanceApprovalStatus::APPROVED,
                    'status' => AdvanceStatus::Active,
                    // Note: treasury_account_id is not yet in the model,
                    // so it will use the default from PostingRule (1120).
                ]);

                Notification::make()
                    ->title('تم تسجيل السُلفة بنجاح')
                    ->success()
                    ->send();
            })
            ->slideOver();
    }
}
