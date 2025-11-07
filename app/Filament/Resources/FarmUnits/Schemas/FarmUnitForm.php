<?php

namespace App\Filament\Resources\FarmUnits\Schemas;

use App\Enums\UnitType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FarmUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->required(),
                TextInput::make('code')
                    ->label('الكود')
                    ->required(),
                Select::make('unit_type')
                    ->label('نوع الوحدة')
                    ->options(UnitType::class)
                    ->required(),
                TextInput::make('capacity')
                    ->label('السعة')
                    ->numeric(),
                TextInput::make('status')
                    ->label('الحالة')
                    ->required()
                    ->default('active'),
                Select::make('current_stock_id')
                    ->label('الرصيد الحالي')
                    ->relationship('currentStock', 'id'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
