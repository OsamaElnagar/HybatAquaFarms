<?php

namespace App\Filament\Resources\SalaryRecords\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\SalaryStatus;
use App\Models\Employee;
use App\Models\SalaryRecord;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SalaryRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('سجل المرتب')
                    ->schema([
                        Select::make('employee_id')
                            ->label('الموظف')
                            ->relationship('employee', 'name')
                            ->default(fn($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get, string $operation) {
                                if ($operation === 'create' && $get('employee_id')) {
                                    self::updateDefaultsFromEmployee($set, $get);
                                }
                            })
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateDefaultsFromEmployee($set, $get);
                            })
                            ->columnSpan(1),

                        DatePicker::make('payment_date')
                            ->label('التاريخ')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if ($state) {
                                    $date = Carbon::parse($state);
                                    $set('pay_period_start', $date->copy()->startOfMonth()->format('Y-m-d'));
                                    $set('pay_period_end', $date->copy()->endOfMonth()->format('Y-m-d'));
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('basic_salary')
                            ->label('قيمة الراتب')
                            ->required()
                            ->numeric()
                            ->suffix(' EGP ')
                            ->minValue(0)
                            ->step(0.01)
                            ->live(true)
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::updateNetSalary($set, $get))
                            ->columnSpan(1),

                        Radio::make('settle_advances')
                            ->label('حالة السُلف')
                            ->options([
                                'full' => 'تسديد الراتب كاملاً (بدون خصم)',
                                'deduct' => 'خصم من السُلف المفتوحة',
                            ])
                            ->default('full')
                            ->dehydrated(false)
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                if ((float) $get('advances_deducted') > 0) {
                                    $set('settle_advances', 'deduct');
                                }
                            })
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($state === 'full') {
                                    $set('advances_deducted', 0);
                                } elseif ($state === 'deduct') {
                                    $employeeId = $get('employee_id');
                                    if ($employeeId) {
                                        $employee = Employee::find($employeeId);
                                        $openAdvances = $employee ? $employee->total_outstanding_advances : 0;
                                        $salary = (float) $get('basic_salary');
                                        $set('advances_deducted', min($openAdvances, $salary));
                                    }
                                }
                                self::updateNetSalary($set, $get);
                            })
                            ->columnSpanFull(),

                        TextInput::make('advances_deducted')
                            ->label('مبلغ الخصم أو التسوية من السُلف')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->visible(fn(Get $get) => $get('settle_advances') === 'deduct')
                            ->live(true)
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::updateNetSalary($set, $get))
                            ->helperText(function (Get $get) {
                                $employeeId = $get('employee_id');
                                if (!$employeeId)
                                    return null;
                                $employee = Employee::find($employeeId);
                                $total = $employee ? $employee->total_outstanding_advances : 0;
                                return "إجمالي السُلف المتبقية على الموظف حالياً: " . number_format($total, 2) . " EGP";
                            })
                            ->columnSpan(1),

                        TextInput::make('net_salary')
                            ->label('الصافي (المبلغ المستحق للموظف)')
                            ->numeric()
                            ->suffix(' EGP ')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->helperText('النقدية الفعلية التي سيتم صرفها بعد تسوية السُلف')
                            ->columnSpan(1),

                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(2)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        // Hidden core fields required by database
                        Hidden::make('pay_period_start')
                            ->default(now()->startOfMonth()->format('Y-m-d')),
                        Hidden::make('pay_period_end')
                            ->default(now()->endOfMonth()->format('Y-m-d')),
                        Hidden::make('bonuses')->default(0),
                        Hidden::make('deductions')->default(0),
                        Hidden::make('unpaid_days')->default(0),
                        Hidden::make('status')->default(SalaryStatus::PAID),
                        Hidden::make('payment_method')->default(PaymentMethod::CASH),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    protected static function updateDefaultsFromEmployee(Set $set, Get $get): void
    {
        $employeeId = $get('employee_id');
        if (!$employeeId) {
            $set('basic_salary', 0);
            return;
        }

        $employee = Employee::find($employeeId);
        if ($employee) {
            // Keep user input if modifying, but for create or first select, default to basic_salary
            $set('basic_salary', (float) $employee->basic_salary ?: 0);

            if ($get('settle_advances') === 'deduct') {
                $openAdvances = $employee->total_outstanding_advances ?? 0;
                $salary = (float) $employee->basic_salary;
                $set('advances_deducted', min($openAdvances, $salary));
            } else {
                $set('advances_deducted', 0);
            }
        }
        self::updateNetSalary($set, $get);
    }

    protected static function updateNetSalary(Set $set, Get $get): void
    {
        $basic = (float) $get('basic_salary');
        $advances = (float) $get('advances_deducted');

        $netBeforeCap = $basic - $advances;

        // Ensure we don't deduct more than the basic salary for the net calculation (optional business logic)
        // If advances_deducted > basic, net would be negative
        $set('net_salary', max(round($netBeforeCap, 2), 0));
    }
}
