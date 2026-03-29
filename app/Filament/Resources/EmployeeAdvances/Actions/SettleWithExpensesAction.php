<?php

namespace App\Filament\Resources\EmployeeAdvances\Actions;

use App\Enums\AccountType;
use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use App\Enums\FarmExpenseType;
use App\Enums\PaymentMethod;
use App\Models\Account;
use App\Models\Batch;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\FarmExpense;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettleWithExpensesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'settleWithExpenses';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('تسوية/سداد سلف بمصاريف')
            ->icon('heroicon-o-document-duplicate')
            ->color('info')
            ->form([
                DatePicker::make('payment_date')
                    ->label('تاريخ التسوية')
                    ->default(now())
                    ->required(),

                Select::make('employee_id')
                    ->label('الموظف')
                    ->options(Employee::query()->active()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->hidden(fn ($record) => $record instanceof EmployeeAdvance || $record instanceof Employee)
                    ->default(fn ($record) => $record instanceof EmployeeAdvance ? $record->employee_id : ($record instanceof Employee ? $record->id : null)),

                Repeater::make('expenses')
                    ->label('المصاريف')
                    ->schema([
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->options(Farm::pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->searchable(),
                        Select::make('batch_id')
                            ->label('الدورة')
                            ->options(fn (callable $get) => Batch::where('farm_id', $get('farm_id'))->pluck('batch_code', 'id'))
                            ->searchable(),
                        Select::make('type')
                            ->label('النوع')
                            ->options(FarmExpenseType::class)
                            ->default(FarmExpenseType::Expense)
                            ->required()
                            ->live(),
                        Select::make('expense_category_id')
                            ->label('تصنيف المصروف')
                            ->options(ExpenseCategory::query()->active()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Select::make('account_id')
                            ->label('الحساب المقابل')
                            ->options(Account::where('type', AccountType::Expense)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->required()
                    ->minItems(1)
                    ->itemLabel(fn (array $state): ?string => isset($state['amount']) ? "مبلغ: {$state['amount']} EGP" : null),
                Textarea::make('notes')
                    ->label('ملاحظات عامة')
                    ->rows(2),
            ])
            ->action(function (array $data, $record = null) {
                // Determine Employee ID
                $employeeId = $data['employee_id'] ?? ($record instanceof EmployeeAdvance ? $record->employee_id : ($record instanceof Employee ? $record->id : null));

                if (! $employeeId) {
                    Notification::make()->title('خطأ')->body('لم يتم تحديد الموظف.')->danger()->send();

                    return;
                }

                $employee = Employee::find($employeeId);
                $totalSettlementAmount = collect($data['expenses'])->sum('amount');

                // Get Active, Approved advances for this employee (FIFO)
                $advances = EmployeeAdvance::where('employee_id', $employeeId)
                    ->where('status', AdvanceStatus::Active)
                    ->where('approval_status', AdvanceApprovalStatus::APPROVED)
                    ->orderBy('request_date', 'asc')
                    ->get();

                if ($advances->isEmpty()) {
                    Notification::make()
                        ->title('لا توجد سلف')
                        ->body('لا يوجد سلف مفتوحة لهذا الموظف لتسويتها.')
                        ->warning()
                        ->send();

                    return;
                }

                $totalDebt = $advances->sum('balance_remaining');
                if ($totalSettlementAmount > $totalDebt) {
                    Notification::make()
                        ->title('تنبيه')
                        ->body("إجمالي المصاريف ({$totalSettlementAmount}) أكبر من إجمالي مديونية السلف ({$totalDebt}). سيتم تسوية المديونية بالكامل فقط.")
                        ->warning()
                        ->send();

                    $totalSettlementAmount = $totalDebt;
                }

                DB::transaction(function () use ($data, $advances, $totalSettlementAmount, $employee) {
                    $remainingToSettle = $totalSettlementAmount;
                    $repayments = [];

                    /** @var EmployeeAdvance $advance */
                    foreach ($advances as $advance) {
                        if ($remainingToSettle <= 0) {
                            break;
                        }

                        $amountForThisAdvance = min($remainingToSettle, $advance->balance_remaining);

                        $repayment = $advance->repayments()->create([
                            'payment_date' => $data['payment_date'],
                            'amount_paid' => $amountForThisAdvance,
                            'payment_method' => PaymentMethod::SETTLEMENT,
                            'balance_remaining' => $advance->balance_remaining - $amountForThisAdvance,
                            'notes' => $data['notes'] ?? 'تسوية سلفة بمصاريف عمل (توزيع تلقائي)',
                        ]);

                        $repayments[] = $repayment;
                        $remainingToSettle -= $amountForThisAdvance;
                    }

                    // 2. Create individual Farm Expenses linked to the first repayment (as an audit trail)
                    $primaryRepayment = $repayments[0] ?? null;

                    foreach ($data['expenses'] as $expenseData) {
                        FarmExpense::create([
                            'farm_id' => $expenseData['farm_id'],
                            'batch_id' => $expenseData['batch_id'] ?? null,
                            'expense_category_id' => $expenseData['expense_category_id'],
                            'account_id' => $expenseData['account_id'],
                            'type' => $expenseData['type'],
                            'amount' => $expenseData['amount'],
                            'date' => $data['payment_date'],
                            'description' => $expenseData['description'] ?? $data['notes'] ?? 'تسوية سلفة لموظف: '.$employee->name,
                            'advance_repayment_id' => $primaryRepayment?->id,
                            'created_by' => Auth::id(),
                            'treasury_account_id' => 34, // سلف الموظفين (Employee Advances Account)
                        ]);
                    }
                });

                Notification::make()
                    ->title('تمت التسوية بنجاح')
                    ->body("تم تسوية مبلغ {$totalSettlementAmount} EGP من سلف الموظف.")
                    ->success()
                    ->send();
            })
            ->slideOver();
    }
}
