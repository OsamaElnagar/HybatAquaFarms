<?php

namespace App\Filament\Resources\FeedStocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        Select::make('feed_warehouse_id')
                            ->label('المستودع')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('المستودع الذي يحتوي على هذا الرصيد')
                            ->columnSpan(1),
                        Select::make('feed_item_id')
                            ->label('صنف العلف')
                            ->relationship('feedItem', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('نوع العلف')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الكميات والتكاليف')
                    ->schema([
                        TextInput::make('quantity_in_stock')
                            ->label('الكمية في المخزون')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->default(0)
                            ->helperText('الكمية المتوفرة حالياً في المخزون')
                            ->columnSpan(1),
                        TextInput::make('average_cost')
                            ->label('متوسط التكلفة')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix(' EGP ')
                            ->helperText('متوسط تكلفة الوحدة الواحدة')
                            ->columnSpan(1),
                        TextInput::make('total_value')
                            ->label('القيمة الإجمالية')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0)
                            ->suffix(' EGP ')
                            ->helperText('القيمة الإجمالية = الكمية × متوسط التكلفة')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
