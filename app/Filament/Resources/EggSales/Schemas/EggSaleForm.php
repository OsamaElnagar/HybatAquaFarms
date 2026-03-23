<?php

namespace App\Filament\Resources\EggSales\Schemas;

use App\Models\Batch;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EggSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        $livewire = $schema->getLivewire();
        $ownerRecord = ($livewire instanceof RelationManager) ? $livewire->getOwnerRecord() : null;
        $isBatchManager = $ownerRecord instanceof Batch;

        return $schema
            ->components([
                Hidden::make('batch_id')
                    ->default($isBatchManager ? $ownerRecord->id : null),

                TextInput::make('sale_number')
                    ->label('رقم البيع')
                    ->disabled()
                    ->dehydrated(),

                Select::make('egg_collection_id')
                    ->label('تجميع البيض')
                    ->relationship(
                        'eggCollection',
                        'collection_number',
                        modifyQueryUsing: fn ($query) => $isBatchManager ? $query->where('batch_id', $ownerRecord->id) : $query
                    )
                    ->required()
                    ->searchable()
                    ->preload(),

                Checkbox::make('is_cash_sale')
                    ->label('بيع نقدي')
                    ->default(false)
                    ->live(),

                Select::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->hidden(fn (Get $get) => $get('is_cash_sale'))
                    ->searchable()
                    ->preload(),

                DatePicker::make('sale_date')
                    ->label('تاريخ البيع')
                    ->default(now())
                    ->required()
                    ->native(false),

                TextInput::make('trays_sold')
                    ->label('الصناديق المباعة')
                    ->required()
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $trays = (int) ($state ?? 0);
                        $eggsPerTray = (int) ($get('eggs_per_tray') ?? 30);
                        $set('total_eggs', $trays * $eggsPerTray);
                    }),

                TextInput::make('eggs_per_tray')
                    ->label('البيض بكل صندوق')
                    ->default(30)
                    ->required()
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $trays = (int) ($get('trays_sold') ?? 0);
                        $eggsPerTray = (int) ($state ?? 30);
                        $set('total_eggs', $trays * $eggsPerTray);
                    }),

                TextInput::make('total_eggs')
                    ->label('إجمالي البيض')
                    ->numeric()
                    ->default(function (Get $get) {
                        $trays = (int) ($get('trays_sold') ?? 0);
                        $eggsPerTray = (int) ($get('eggs_per_tray') ?? 30);

                        return $trays * $eggsPerTray;
                    }),

                TextInput::make('unit_price')
                    ->label('سعر الصندوق')
                    ->required()
                    ->numeric()
                    ->suffix('ج.م.'),

                TextInput::make('transport_cost')
                    ->label('تكلفة النقل')
                    ->numeric()
                    ->suffix('ج.م.'),

                TextInput::make('discount_amount')
                    ->label('الخصم')
                    ->numeric()
                    ->suffix('ج.م.'),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
