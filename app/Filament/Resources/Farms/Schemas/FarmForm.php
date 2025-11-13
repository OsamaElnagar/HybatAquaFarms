<?php

namespace App\Filament\Resources\Farms\Schemas;

use App\Enums\FarmStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FarmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات أساسية')
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('كود فريد للمزرعة لسهولة التعرف عليها')
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('اسم المزرعة كما سيظهر في التقارير')
                            ->columnSpan(1),
                        TextInput::make('size')
                            ->label('المساحة')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('المساحة بوحدة القياس المتبعة (مثلاً: فدان)')
                            ->columnSpan(1),
                        TextInput::make('location')
                            ->label('الموقع')
                            ->maxLength(255)
                            ->helperText('وصف مختصر لموقع المزرعة أو العنوان')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الحالة والإدارة')
                    ->schema([
                        Select::make('status')
                            ->label('الحالة')
                            ->options(FarmStatus::class)
                            ->default(FarmStatus::Active)
                            ->required()
                            ->native(false)
                            ->helperText('حالة تشغيل المزرعة: نشط، غير نشط، أو تحت الصيانة')
                            ->columnSpan(1),
                        DatePicker::make('established_date')
                            ->label('تاريخ التأسيس')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ تأسيس المزرعة (اختياري)')
                            ->columnSpan(1),
                        Select::make('manager_id')
                            ->label('المدير')
                            ->relationship('manager', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('اختر موظف لإدارة هذه المزرعة (اختياري)')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('ملاحظات إضافية')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('أي معلومات أو ملاحظات إضافية متعلقة بالمزرعة')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
