<?php

namespace App\Filament\Resources\FarmUnits\Schemas;

use App\Enums\FarmStatus;
use App\Enums\UnitType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FarmUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر المزرعة التي تنتمي إليها هذه الوحدة')
                            ->columnSpan(1),
                        TextInput::make('code')
                            ->label('الكود')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('يتم توليده تلقائياً')
                            ->columnSpan(1),
                        Select::make('unit_type')
                            ->label('نوع الوحدة')
                            ->options(UnitType::class)
                            ->required()
                            ->native(false)
                            ->helperText('نوع الوحدة: حوض، خزان، أو قفص')
                            ->columnSpan(1),
                        TextInput::make('capacity')
                            ->label('السعة')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('السعة القصوى للوحدة (اختياري)')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الحالة والملاحظات')
                    ->schema([
                        Select::make('status')
                            ->label('الحالة')
                            ->options(FarmStatus::class)
                            ->required()
                            ->default(FarmStatus::Active)
                            ->native(false)
                            ->helperText('حالة الوحدة: نشط، غير نشط، أو تحت الصيانة')
                            ->columnSpan(1),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('أي ملاحظات إضافية حول الوحدة')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
