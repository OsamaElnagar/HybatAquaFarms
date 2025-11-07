<?php

namespace App\Filament\Resources\BatchMovements\Schemas;

use App\Enums\MovementType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('batch_id')
                    ->relationship('batch', 'id')
                    ->required(),
                Select::make('movement_type')
                    ->options(MovementType::class)
                    ->required(),
                Select::make('from_farm_id')
                    ->relationship('fromFarm', 'name'),
                Select::make('to_farm_id')
                    ->relationship('toFarm', 'name'),
                Select::make('from_unit_id')
                    ->relationship('fromUnit', 'id'),
                Select::make('to_unit_id')
                    ->relationship('toUnit', 'id'),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('weight')
                    ->numeric(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('reason'),
                TextInput::make('recorded_by')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
