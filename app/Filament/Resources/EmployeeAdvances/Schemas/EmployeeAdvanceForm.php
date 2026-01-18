<?php

namespace App\Filament\Resources\EmployeeAdvances\Schemas;

use App\Enums\AdvanceApprovalStatus;
use App\Enums\AdvanceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EmployeeAdvanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات السلفة')
                    ->schema([
                        TextInput::make('advance_number')
                            ->label('رقم السلفة')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('يتم توليده تلقائياً')
                            ->columnSpan(1),
                        Select::make('employee_id')
                            ->label('الموظف')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('الموظف الذي حصل على السلفة')
                            ->columnSpan(1),
                        DatePicker::make('request_date')
                            ->label('تاريخ الطلب')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ تقديم طلب السلفة')
                            ->columnSpan(1),
                        TextInput::make('amount')
                            ->label('مبلغ السلفة')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->afterStateUpdated(fn (Set $set, Get $get) => $set('balance_remaining', $get('amount')))
                            ->live(true)
                            ->helperText('قيمة السلفة الإجمالية')
                            ->columnSpan(1),
                        Textarea::make('reason')
                            ->label('سبب السلفة')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('أضف سبب أو ملاحظات حول سبب طلب السلفة')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('الموافقة والصرف')
                    ->schema([
                        Select::make('approval_status')
                            ->label('حالة الموافقة')
                            ->options(AdvanceApprovalStatus::class)
                            ->native(false)
                            ->required()
                            ->default(AdvanceApprovalStatus::APPROVED)
                            ->helperText('حالة طلب السلفة الحالية')
                            ->columnSpan(1),
                        // Select::make('approved_by')
                        //     ->label('وافق بواسطة')
                        //     ->relationship('approvedBy', 'name')
                        //     ->searchable()
                        //     ->preload()
                        //     ->helperText('المستخدم الذي وافق على السلفة (إن وُجد)')
                        //     ->columnSpan(1),
                        DatePicker::make('approved_date')
                            ->label('تاريخ الموافقة')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('يُملأ بعد الموافقة على السلفة')
                             ->default(now())
                            ->columnSpan(1),
                        DatePicker::make('disbursement_date')
                            ->label('تاريخ الصرف')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                             ->default(now())
                            ->helperText('التاريخ الذي تم فيه صرف السلفة فعلياً')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
                Section::make('خطة السداد والمتابعة')
                    ->schema([
                        TextInput::make('installments_count')
                            ->label('عدد الأقساط')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('عدد الأقساط المتفق عليها')
                            ->columnSpan(1),
                        TextInput::make('installment_amount')
                            ->label('مبلغ القسط')
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('قيمة كل قسط (يُملأ تلقائياً إذا وُجد نظام أقساط)')
                            ->columnSpan(1),
                        TextInput::make('balance_remaining')
                            ->label('الرصيد المتبقي')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->step(0.01)
                            ->suffix(' EGP ')
                            ->helperText('المبلغ المتبقي بعد عمليات السداد')
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('الحالة الحالية')
                            ->options(AdvanceStatus::class)
                            ->default(AdvanceStatus::Active)
                            ->native(false)
                            ->required()
                            ->helperText('وضع السلفة الحالي بالنسبة للسداد')
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('أي تفاصيل إضافية تخص السلفة أو خطة السداد')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
