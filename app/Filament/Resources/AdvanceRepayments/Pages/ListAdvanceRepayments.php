<?php

namespace App\Filament\Resources\AdvanceRepayments\Pages;

use App\Enums\PaymentMethod;
use App\Filament\Resources\AdvanceRepayments\AdvanceRepaymentResource;
use App\Filament\Resources\AdvanceRepayments\Widgets\AdvanceRepaymentsStatsWidget;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\SalaryRecord;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListAdvanceRepayments extends ListRecords
{
    protected static string $resource = AdvanceRepaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->form([
                    Select::make('employee_id')
                        ->label('الموظف')
                        ->options(Employee::whereHas('advances', fn ($q) => $q->where('balance_remaining', '>', 0))->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live(true),
                    DatePicker::make('payment_date')
                        ->label('تاريخ السداد')
                        ->required()
                        ->default(now())
                        ->displayFormat('Y-m-d')
                        ->native(false),
                    TextInput::make('amount_paid')
                        ->label('المبلغ المدفوع')
                        ->required()
                        ->numeric()
                        ->suffix(' EGP ')
                        ->minValue(0.01)
                        ->step(0.01)
                        ->maxValue(fn (Get $get) => EmployeeAdvance::where('employee_id', $get('employee_id'))->where('balance_remaining', '>', 0)->sum('balance_remaining'))
                        ->helperText(fn (Get $get) => 'المتبقي الإجمالي للسلف: '.number_format(EmployeeAdvance::where('employee_id', $get('employee_id'))->where('balance_remaining', '>', 0)->sum('balance_remaining'), 2))
                        ->disabled(fn (Get $get) => blank($get('employee_id'))),
                    Select::make('payment_method')
                        ->label('طريقة السداد')
                        ->options(PaymentMethod::class)
                        ->required()
                        ->live(true)
                        ->disabled(fn (Get $get) => blank($get('employee_id'))),
                    Select::make('salary_record_id')
                        ->label('سجل المرتب المرتبط')
                        ->relationship(
                            name: 'salaryRecord',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->where('status', 'paid')
                                ->where('employee_id', $get('employee_id'))
                        )
                        ->getOptionLabelFromRecordUsing(fn (SalaryRecord $record) => '#'.$record->id.' - '.$record->net_salary.' EGP')
                        ->visible(fn (Get $get) => $get('payment_method') === PaymentMethod::SALARY_DEDUCTION->value || $get('payment_method') === PaymentMethod::SALARY_DEDUCTION),
                    Textarea::make('notes')
                        ->label('ملاحظات إضافية')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])
                ->using(function (array $data, string $model): Model {
                    $amountToPay = (float) $data['amount_paid'];

                    $advances = EmployeeAdvance::where('employee_id', $data['employee_id'])
                        ->where('balance_remaining', '>', 0)
                        ->orderBy('approved_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                    $lastRepayment = null;

                    foreach ($advances as $advance) {
                        if ($amountToPay <= 0) {
                            break;
                        }

                        $paidForThisAdvance = min((float) $advance->balance_remaining, $amountToPay);

                        $lastRepayment = $model::create([
                            'employee_advance_id' => $advance->id,
                            'amount_paid' => $paidForThisAdvance,
                            'payment_date' => $data['payment_date'],
                            'payment_method' => $data['payment_method'],
                            'salary_record_id' => $data['salary_record_id'] ?? null,
                            'notes' => count($advances) > 1 ? ($data['notes'].' (سداد مجمع)') : $data['notes'],
                            'balance_remaining' => max(0, $advance->balance_remaining - $paidForThisAdvance),
                        ]);

                        $amountToPay -= $paidForThisAdvance;
                    }

                    return $lastRepayment ?? new $model;
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdvanceRepaymentsStatsWidget::class,
        ];
    }
}
