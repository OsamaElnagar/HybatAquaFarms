<?php

namespace App\Filament\Resources\SalaryRecords\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\SalaryStatus;
use App\Models\Employee;
use App\Models\SalaryRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class SalaryRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الموظف والفترة')
                    ->schema([
                        Select::make('employee_id')
                            ->label('الموظف')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر الموظف الذي يتم إعداد كشف المرتب له')
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateBasicFromPeriod($set, $get))
                            ->columnSpan(1),
                        DatePicker::make('pay_period_start')
                            ->label('بداية الفترة')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ بداية فترة الإستحقاق')
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateBasicFromPeriod($set, $get))
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $employeeId = (int) $get('employee_id');
                                    $start = $get('pay_period_start');
                                    $end = $get('pay_period_end');

                                    if (! $employeeId || ! $start || ! $end) {
                                        return;
                                    }

                                    $overlapCount = SalaryRecord::query()
                                        ->where('employee_id', $employeeId)
                                        ->where(function ($q) use ($start, $end) {
                                            $q->whereBetween('pay_period_start', [$start, $end])
                                                ->orWhereBetween('pay_period_end', [$start, $end])
                                                ->orWhere(function ($qq) use ($start, $end) {
                                                    $qq->where('pay_period_start', '<=', $start)
                                                        ->where('pay_period_end', '>=', $end);
                                                });
                                        })
                                        ->count();

                                    if ($overlapCount > 0) {
                                        $fail('يوجد سجل مرتب آخر لنفس الموظف يتداخل مع هذه الفترة.');
                                    }
                                };
                            }, Operation::class === Operation::Create)
                            ->columnSpan(1),
                        DatePicker::make('pay_period_end')
                            ->label('نهاية الفترة')
                            ->required()
                            ->default(now()->endOfMonth())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->rule('after_or_equal:pay_period_start')
                            ->helperText('تاريخ نهاية فترة الإستحقاق')
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateBasicFromPeriod($set, $get))
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    $employeeId = (int) $get('employee_id');
                                    $start = $get('pay_period_start');
                                    $end = $get('pay_period_end');

                                    if (! $employeeId || ! $start || ! $end) {
                                        return;
                                    }

                                    $overlapCount = SalaryRecord::query()
                                        ->where('employee_id', $employeeId)
                                        ->where(function ($q) use ($start, $end) {
                                            $q->whereBetween('pay_period_start', [$start, $end])
                                                ->orWhereBetween('pay_period_end', [$start, $end])
                                                ->orWhere(function ($qq) use ($start, $end) {
                                                    $qq->where('pay_period_start', '<=', $start)
                                                        ->where('pay_period_end', '>=', $end);
                                                });
                                        })
                                        ->count();

                                    if ($overlapCount > 0) {
                                        $fail('يوجد سجل مرتب آخر لنفس الموظف يتداخل مع هذه الفترة.');
                                    }
                                };
                            }, Operation::class === Operation::Create)
                            ->columnSpan(1),
                        TextInput::make('unpaid_days')
                            ->label('أيام غير مدفوعة')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(1)
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateBasicFromPeriod($set, $get))
                            ->helperText('أيام يتم خصمها من الحساب (غياب/إجازة غير مدفوعة)')
                            ->columnSpan(1),
                        TextInput::make('per_day_rate')
                            ->label('قيمة اليوم')
                            ->suffix(' EGP ')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('يُحسب تلقائياً = المرتب الشهري / 30')
                            ->columnSpan(1),
                        TextInput::make('working_days')
                            ->label('الأيام المحتسبة')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('عدد الأيام بين البداية والنهاية مطروحاً منها الأيام غير المدفوعة')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('التفاصيل المالية')
                    ->schema([
                        TextInput::make('basic_salary')
                            ->label('الراتب الأساسي')
                            ->required()
                            ->numeric()
                            ->suffix(' EGP ')
                            ->minValue(0)
                            ->step(0.01)
                            ->live(true)

                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateNetSalary($set, $get))
                            ->helperText('الراتب الأساسي قبل الإضافات والخصومات')
                            ->columnSpan(1),
                        TextInput::make('bonuses')
                            ->label('المكافآت')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateNetSalary($set, $get))
                            ->helperText('أي مكافآت أو حوافز إضافية')
                            ->columnSpan(1),
                        TextInput::make('deductions')
                            ->label('الخصومات')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateNetSalary($set, $get))
                            ->helperText('الخصومات: غياب، تأخير، جزاءات ...')
                            ->columnSpan(1),
                        TextInput::make('advances_deducted')
                            ->label('السُلف المخصومة')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateNetSalary($set, $get))
                            ->helperText('مبالغ السُلف التي تم خصمها من مرتب هذا الشهر')
                            ->columnSpan(1),
                        TextInput::make('net_salary')
                            ->label('صافي المرتب')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->disabled()
                            ->dehydrated()
                            ->reactive()
                            ->helperText('يتم احتسابه تلقائياً: الأساسي + المكافآت - الخصومات - السُلف')
                            ->afterStateHydrated(fn ($state, Set $set, Get $get) => self::updateNetSalary($set, $get))
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
                Section::make('الدفع والمتابعة')
                    ->schema([
                        DatePicker::make('payment_date')
                            ->label('تاريخ الدفع')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->default(now())
                            ->helperText('التاريخ المتوقع/الفعل للدفع')
                            ->columnSpan(1),
                        Select::make('payment_method')
                            ->options(PaymentMethod::class)
                            ->label('طريقة الدفع')
                            ->searchable()
                            ->helperText('كيف تم دفع المرتب؟')
                            ->columnSpan(1),
                        TextInput::make('payment_reference')
                            ->label('رقم المرجع')
                            ->maxLength(100)
                            ->placeholder('رقم العملية البنكية أو المرجع الداخلي')
                            ->helperText('يسهل تتبع عملية الدفع')
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('حالة الدفع')
                            ->options(SalaryStatus::class)
                            ->native(false)
                            ->required()
                            ->default(SalaryStatus::PAID)
                            ->helperText('الحالة الحالية لسجل المرتب')
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->helperText('أي تفاصيل إضافية أو مرفقات متعلقة بالكشف'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }

    protected static function updateBasicFromPeriod(Set $set, Get $get): void
    {
        $employeeId = (int) $get('employee_id');
        $start = $get('pay_period_start');
        $end = $get('pay_period_end');
        $unpaid = (int) ($get('unpaid_days') ?? 0);

        if (! $employeeId || ! $start || ! $end) {
            // If employee is selected but dates are incomplete, default basic to fixed monthly salary
            if ($employeeId) {
                $employee = Employee::find($employeeId);
                if ($employee) {
                    $perDay = (float) $employee->basic_salary / 30;
                    $set('per_day_rate', round($perDay));
                    $set('working_days', null);
                    $set('basic_salary', (float) $employee->basic_salary);
                    self::updateNetSalary($set, $get);
                }
            }

            return;
        }

        $employee = Employee::find($employeeId);
        if (! $employee) {
            return;
        }

        // 30-day month per business rule
        $perDay = (float) $employee->basic_salary / 30;

        // Prorate working days across months to ensure a full month equals 30
        $startDate = \Illuminate\Support\Carbon::parse($start)->startOfDay();
        $endDate = \Illuminate\Support\Carbon::parse($end)->startOfDay();

        // Ensure chronological order
        if ($endDate->lt($startDate)) {
            $set('per_day_rate', round($perDay));
            $set('working_days', 0);
            $set('basic_salary', 0);
            self::updateNetSalary($set, $get);

            return;
        }

        $cursor = $startDate->copy()->startOfDay();
        $totalWorking = 0.0;
        while ($cursor->lte($endDate)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $segmentStart = $cursor->copy();
            $segmentEnd = $endDate->min($monthEnd);

            $daysInSegment = $segmentStart->diffInDays($segmentEnd) + 1; // inclusive
            $daysInMonth = $monthStart->daysInMonth;

            // Portion of 30-day base for this month segment
            $portion = (30 * $daysInSegment) / $daysInMonth;
            $totalWorking += $portion;

            // Move to first day of next month
            $cursor = $monthEnd->addDay()->startOfDay();
        }

        // Subtract unpaid days and clamp
        $totalWorking = max($totalWorking - $unpaid, 0);

        // Round working days to 2 decimals for display
        $workingForDisplay = round($totalWorking);
        $basic = max(round($perDay * $totalWorking), 0);

        $set('per_day_rate', round($perDay));
        $set('working_days', $workingForDisplay);
        $set('basic_salary', $basic);

        self::updateNetSalary($set, $get);
    }

    protected static function updateNetSalary(Set $set, Get $get): void
    {
        $basic = (float) $get('basic_salary');
        $bonuses = (float) $get('bonuses');
        $deductions = (float) $get('deductions');
        $advances = (float) $get('advances_deducted');

        $net = $basic + $bonuses - $deductions - $advances;

        $set('net_salary', max(round($net), 0));
    }
}
