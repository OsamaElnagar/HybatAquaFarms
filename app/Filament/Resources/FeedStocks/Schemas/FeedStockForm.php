<?php

namespace App\Filament\Resources\FeedStocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('feed_warehouse_id')
                    ->required()
                    ->numeric(),
                Select::make('feed_item_id')
                    ->relationship('feedItem', 'name')
                    ->required(),
                TextInput::make('quantity_in_stock')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('average_cost')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_value')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
