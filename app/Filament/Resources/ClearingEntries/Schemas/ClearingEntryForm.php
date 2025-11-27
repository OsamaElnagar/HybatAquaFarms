<?php

namespace App\Filament\Resources\ClearingEntries\Schemas;

use App\Models\Factory;
use App\Models\Trader;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClearingEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($state) {
                            $trader = Trader::find($state);
                            $set('trader_balance', number_format($trader->outstanding_balance));
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->disabled(fn ($record) => $record?->journal_entry_id !== null),
                Placeholder::make('trader_balance')
                    ->label('رصيد التاجر المستحق')
                    ->content(function ($get) {
                        $traderId = $get('trader_id');
                        if ($traderId) {
                            $trader = Trader::find($traderId);

                            return number_format($trader->outstanding_balance ?? 0).' جنيه';
                        }

                        return '0.00 جنيه';
                    })
                    ->visible(fn ($get) => filled($get('trader_id'))),
                Select::make('factory_id')
                    ->label('المصنع')
                    ->relationship('factory', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($state) {
                            $factory = Factory::find($state);
                            $set('factory_balance', number_format($factory->outstanding_balance));
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->disabled(fn ($record) => $record?->journal_entry_id !== null),
                Placeholder::make('factory_balance')
                    ->label('رصيد المصنع المستحق عليه')
                    ->content(function ($get) {
                        $factoryId = $get('factory_id');
                        if ($factoryId) {
                            $factory = Factory::find($factoryId);

                            return number_format($factory->outstanding_balance ?? 0).' جنيه';
                        }

                        return '0.00 جنيه';
                    })
                    ->visible(fn ($get) => filled($get('factory_id'))),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('ج.م')
                    // ->suffix('')
                    ->minValue(0.01)
                    ->step(0.01)
                    ->disabled(fn ($record) => $record?->journal_entry_id !== null),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->maxLength(500),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull()
                    ->maxLength(1000),
            ]);
    }
}
