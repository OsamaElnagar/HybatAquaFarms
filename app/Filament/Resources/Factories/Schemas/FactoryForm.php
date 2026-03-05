<?php

namespace App\Filament\Resources\Factories\Schemas;

use App\Enums\FactoryType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class FactoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('بيانات المصنع')->schema([
                    TextInput::make('code')
                        ->label('الكود')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('يتم توليده تلقائياً'),
                    TextInput::make('name')
                        ->label('الاسم')
                        ->required()
                        ->helperText('أدخل الاسم الكامل للمصنع'),
                    Select::make(name: 'type')
                        ->label('نوع المصنع')
                        ->options(FactoryType::class)
                        ->required()
                        ->live()
                        ->helperText('اختر نوع المصنع (مفرخ أو اعلاف)'),
                    Select::make('supplier_activity_id')
                        ->label('نشاط المورد')
                        ->relationship('supplierActivity', 'name')
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('النشاط')
                                ->required()
                                ->maxLength(255)
                                ->unique('supplier_activities', 'name'),
                        ])
                        ->required(fn(Get $get) => $get('type') === FactoryType::SUPPLIER)
                        ->visible(fn(Get $get) => $get('type') === FactoryType::SUPPLIER)
                        ->helperText('اختر نشاط المورد'),
                    TextInput::make('phone')
                        ->label('الهاتف')
                        ->tel()
                        ->helperText('رقم هاتف أساسي للتواصل مع المصنع'),
                    Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true)
                        ->required()
                        ->helperText('تفعيل/تعطيل المصنع من النظام'),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->helperText('أي معلومات إضافية أو ملاحظات خاصة بالمصنع')
                        ->columnSpanFull(),
                ])->columnSpanFull()->columns(2),
            ]);
    }
}
