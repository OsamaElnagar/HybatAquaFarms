<?php

namespace App\Filament\Resources\AdvanceRepayments\Schemas;

use App\Enums\PaymentMethod;
use App\Models\EmployeeAdvance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AdvanceRepaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات السلفة')
                    ->schema([
                        Select::make('employee_advance_id')
                            ->label('السلفة')
                            ->relationship('employeeAdvance', 'advance_number', fn ($query) => $query->where('balance_remaining', '>', 0))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر السلفة التي سيتم سداد جزء منها')
                            ->getOptionLabelFromRecordUsing(fn (EmployeeAdvance $record) => "{$record->advance_number} - {$record->employee->name} (متبقي: ".number_format($record->balance_remaining).' ج.م)')
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, $state) => self::syncRemainingFromAdvance($set, $state)),
                        DatePicker::make('payment_date')
                            ->label('تاريخ السداد')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ دفع هذا القسط'),
                        TextEntry::make('advance_overview')
                            ->label('بيانات السلفة')
                            ->state(fn (Get $get) => self::advanceSummary($get('employee_advance_id')))
                            ->hidden(fn (Get $get) => blank($get('employee_advance_id')))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('تفاصيل السداد')
                    ->schema([
                        TextInput::make('amount_paid')
                            ->label('المبلغ المدفوع')
                            ->required()
                            ->numeric()
                            ->prefix('ج.م')
                            ->minValue(0.01)
                            ->step(0.01)
                            ->reactive()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::updateRemaining($set, $get))
                            ->helperText('قيمة القسط المدفوع حالياً'),
                        Select::make('payment_method')
                            ->label('طريقة السداد')
                            ->options(PaymentMethod::class)
                            ->required()
                            ->helperText('كيف تم سداد السلفة؟')
                            ->reactive(),
                        Select::make('salary_record_id')
                            ->label('سجل المرتب المرتبط')
                            ->relationship('salaryRecord', 'id', fn ($query) => $query->where('status', 'paid'))
                            ->searchable()
                            ->helperText('اختر سجل المرتب الذي تم خصم السلفة منه (إن وجد)')
                            ->visible(fn (Get $get) => $get('payment_method') === PaymentMethod::SALARY_DEDUCTION),
                        TextInput::make('balance_remaining')
                            ->label('الرصيد المتبقي بعد السداد')
                            ->numeric()
                            ->prefix('ج.م')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('يتم احتسابه تلقائياً بناءً على الرصيد المتبقي في السلفة')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
                Section::make('ملاحظات')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('أضف أي تفاصيل أو مرجع إضافي لهذا السداد')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }

    protected static function syncRemainingFromAdvance(Set $set, ?int $advanceId): void
    {
        if (! $advanceId) {
            $set('balance_remaining', null);

            return;
        }

        $advance = EmployeeAdvance::find($advanceId);
        if (! $advance) {
            $set('balance_remaining', null);

            return;
        }

        $set('balance_remaining', round($advance->balance_remaining));
    }

    protected static function updateRemaining(Set $set, Get $get): void
    {
        $advanceId = $get('employee_advance_id');
        $amountPaid = (float) $get('amount_paid');

        if (! $advanceId) {
            return;
        }

        $advance = EmployeeAdvance::find($advanceId);
        if (! $advance) {
            return;
        }

        $remaining = max($advance->balance_remaining - $amountPaid, 0);

        $set('balance_remaining', round($remaining));
    }

    protected static function advanceSummary(?int $advanceId): ?string
    {
        if (! $advanceId) {
            return null;
        }

        $advance = EmployeeAdvance::with('employee')->find($advanceId);
        if (! $advance) {
            return null;
        }

        $approvedDate = optional($advance->approved_date)->format('Y-m-d') ?? 'غير محدد';
        $total = number_format($advance->amount_paid);
        $remaining = number_format($advance->balance_remaining);

        return "الموظف: {$advance->employee->name} | المبلغ الكلي: {$total} ج.م | المتبقي: {$remaining} ج.م | تاريخ الموافقة: {$approvedDate}";
    }
}
