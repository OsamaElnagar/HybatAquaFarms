<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\PaymentMethod;
use App\Models\EmployeeAdvance;
use App\Models\SalaryRecord;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdvanceRepaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'advanceRepayments';

    protected static ?string $title = 'سداد السُلف';

    protected static ?string $modelLabel = 'سداد سلفة';

    protected static ?string $pluralModelLabel = 'سداد السُلف';

    public function form(Schema $schema): Schema
    {
        $employeeId = $this->getOwnerRecord()->id;

        return $schema
            ->components([
                Section::make('بيانات السلفة')
                    ->schema([
                        Select::make('employee_advance_id')
                            ->label('السلفة')
                            ->relationship(
                                name: 'employeeAdvance',
                                titleAttribute: 'advance_number',
                                modifyQueryUsing: fn(Builder $query) => $query
                                    ->where('employee_id', $employeeId)
                                    ->where('balance_remaining', '>', 0),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر السلفة التي سيتم سداد جزء منها')
                            ->getOptionLabelFromRecordUsing(fn(EmployeeAdvance $record) => "{$record->advance_number} (متبقي: " . number_format($record->balance_remaining) . ' EGP)')
                            ->live(true)
                            ->afterStateUpdated(fn(Set $set, $state) => self::syncRemainingFromAdvance($set, $state)),
                        DatePicker::make('payment_date')
                            ->label('تاريخ السداد')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ دفع هذا القسط'),
                        TextEntry::make('advance_overview')
                            ->label('بيانات السلفة')
                            ->state(fn(Get $get) => self::advanceSummary($get('employee_advance_id')))
                            ->hidden(fn(Get $get) => blank($get('employee_advance_id')))
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
                            ->suffix(' EGP ')
                            ->minValue(0.01)
                            ->step(0.01)
                            ->live(true)
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::updateRemaining($set, $get))
                            ->helperText('قيمة القسط المدفوع حالياً'),
                        Select::make('payment_method')
                            ->label('طريقة السداد')
                            ->options(PaymentMethod::class)
                            ->required()
                            ->helperText('كيف تم سداد السلفة؟')
                            ->live(true),
                        Select::make('salary_record_id')
                            ->label('سجل المرتب المرتبط')
                            ->relationship(
                                name: 'salaryRecord',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn(Builder $query, Get $get) => $query
                                    ->where('status', 'paid')
                                    ->where('employee_id', $employeeId),
                            )
                            ->getOptionLabelFromRecordUsing(fn(SalaryRecord $record) => '#' . $record->id . ' - ' . $record->net_salary . ' EGP')
                            ->helperText('اختر سجل المرتب الذي تم خصم السلفة منه (إن وجد)')
                            ->visible(fn(Get $get) => $get('payment_method') === PaymentMethod::SALARY_DEDUCTION),
                        TextInput::make('balance_remaining')
                            ->label('الرصيد المتبقي بعد السداد')
                            ->numeric()
                            ->suffix(' EGP ')
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

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['employeeAdvance', 'salaryRecord']))
            ->columns([
                TextColumn::make('employeeAdvance.advance_number')
                    ->label('رقم السلفة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('المبلغ المدفوع')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('إجمالي المبلغ المدفوع')
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
                TextColumn::make('balance_remaining')
                    ->label('الرصيد المتبقي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn(float $state) => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                // TextColumn::make('employeeAdvance.balance_remaining')
                //     ->label('الرصيد المتبقي للسلفة')
                //     ->money('EGP', locale: 'en', decimalPlaces: 0)
                //     ->toggleable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('salaryRecord.id')
                    ->label('رقم السجل المرتب')
                    ->formatStateUsing(fn($state) => $state ? '#' . $state : 'السجل غير موجود')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_advance_id')
                    ->label('رقم السلفة')
                    ->relationship('employeeAdvance', 'advance_number')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(PaymentMethod::class)
                    ->native(false),
                Filter::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->schema([
                        DatePicker::make('from')->label('من')->displayFormat('Y-m-d')->native(false),
                        DatePicker::make('to')->label('إلى')->displayFormat('Y-m-d')->native(false),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder => $query
                            ->when($data['from'] ?? null, fn(Builder $q, $date) => $q->whereDate('payment_date', '>=', $date))
                            ->when($data['to'] ?? null, fn(Builder $q, $date) => $q->whereDate('payment_date', '<=', $date)),
                    ),
            ])
            ->defaultSort('payment_date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function syncRemainingFromAdvance(Set $set, ?int $advanceId): void
    {
        if (!$advanceId) {
            $set('balance_remaining', null);

            return;
        }

        $advance = EmployeeAdvance::find($advanceId);
        if (!$advance) {
            $set('balance_remaining', null);

            return;
        }

        $set('balance_remaining', round($advance->balance_remaining));
    }

    protected static function updateRemaining(Set $set, Get $get): void
    {
        $advanceId = $get('employee_advance_id');
        $amountPaid = (float) $get('amount_paid');

        if (!$advanceId) {
            return;
        }

        $advance = EmployeeAdvance::find($advanceId);
        if (!$advance) {
            return;
        }

        $remaining = max($advance->balance_remaining - $amountPaid, 0);

        $set('balance_remaining', round($remaining));
    }

    protected static function advanceSummary(?int $advanceId): ?string
    {
        if (!$advanceId) {
            return null;
        }

        $advance = EmployeeAdvance::find($advanceId);
        if (!$advance) {
            return null;
        }

        $approvedDate = optional($advance->approved_date)->format('Y-m-d') ?? 'غير محدد';
        $total = number_format($advance->amount);
        $remaining = number_format($advance->balance_remaining);

        return "السلفة: {$advance->advance_number} | المبلغ الكلي: {$total} EGP | المتبقي: {$remaining} EGP | تاريخ الموافقة: {$approvedDate}";
    }
}
