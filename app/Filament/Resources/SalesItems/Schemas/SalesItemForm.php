<?php

namespace App\Filament\Resources\SalesItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SalesItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sales_order_id')
                    ->relationship('salesOrder', 'id')
                    ->required(),
                Select::make('batch_id')
                    ->relationship('batch', 'id'),
                Select::make('species_id')
                    ->relationship('species', 'name'),
                TextInput::make('description'),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('weight')
                    ->numeric(),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric(),
                TextInput::make('total_price')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
