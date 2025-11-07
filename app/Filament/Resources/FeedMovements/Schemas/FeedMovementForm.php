<?php

namespace App\Filament\Resources\FeedMovements\Schemas;

use App\Enums\FeedMovementType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('movement_type')
                    ->label('نوع الحركة')
                    ->options(FeedMovementType::class)
                    ->required(),
                Select::make('feed_item_id')
                    ->label('صنف العلف')
                    ->relationship('feedItem', 'name')
                    ->required(),
                Select::make('from_warehouse_id')
                    ->label('من المخزن')
                    ->relationship('fromWarehouse', 'name'),
                Select::make('to_warehouse_id')
                    ->label('إلى المخزن')
                    ->relationship('toWarehouse', 'name'),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required(),
                TextInput::make('quantity')
                    ->label('الكمية')
                    ->required()
                    ->numeric(),
                TextInput::make('unit_cost')
                    ->label('تكلفة الوحدة')
                    ->numeric(),
                TextInput::make('total_cost')
                    ->label('التكلفة الإجمالية')
                    ->numeric(),
                Select::make('factory_id')
                    ->label('المصنع')
                    ->relationship('factory', 'name'),
                TextInput::make('source_type')
                    ->label('نوع المصدر')
                    ->required(),
                TextInput::make('source_id')
                    ->label('المصدر')
                    ->required()
                    ->numeric(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                TextInput::make('recorded_by')
                    ->label('سجل بواسطة')
                    ->numeric(),
            ]);
    }
}
