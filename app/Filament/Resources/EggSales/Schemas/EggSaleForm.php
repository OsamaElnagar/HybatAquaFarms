<?php

namespace App\Filament\Resources\EggSales\Schemas;

use App\Models\Batch;
use App\Models\EggCollection;
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

                Select::make('egg_collection_ids')
                    ->label('تجميع البيض')
                    ->multiple()
                    ->options(function () use ($isBatchManager, $ownerRecord) {
                        $query = EggCollection::query()
                            ->whereNull('egg_sale_id')
                            ->whereDoesntHave('eggSale');

                        if ($isBatchManager) {
                            $query->where('batch_id', $ownerRecord->id);
                        }

                        return $query->pluck('collection_number', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $collectionIds = is_array($state) ? $state : [];
                        $collections = EggCollection::whereIn('id', $collectionIds)->get();

                        $totalTrays = $collections->sum('total_trays');
                        $totalEggs = $collections->sum('total_eggs');

                        $set('trays_sold', $totalTrays);
                        $set('total_eggs', $totalEggs);
                    }),

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

                        $unitPrice = (float) ($get('unit_price') ?? 0);
                        $subtotal = $trays * $unitPrice;
                        $set('subtotal', $subtotal);

                        $transportCost = (float) ($get('transport_cost') ?? 0);
                        $taxAmount = (float) ($get('tax_amount') ?? 0);
                        $discountAmount = (float) ($get('discount_amount') ?? 0);
                        $netAmount = $subtotal + $transportCost + $taxAmount - $discountAmount;
                        $set('net_amount', $netAmount);
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
                    ->suffix('ج.م.')
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $trays = (int) ($get('trays_sold') ?? 0);
                        $unitPrice = (float) ($state ?? 0);
                        $subtotal = $trays * $unitPrice;
                        $set('subtotal', $subtotal);

                        $transportCost = (float) ($get('transport_cost') ?? 0);
                        $taxAmount = (float) ($get('tax_amount') ?? 0);
                        $discountAmount = (float) ($get('discount_amount') ?? 0);
                        $netAmount = $subtotal + $transportCost + $taxAmount - $discountAmount;
                        $set('net_amount', $netAmount);
                    }),

                TextInput::make('subtotal')
                    ->label('المجموع الفرعي')
                    ->numeric()
                    ->suffix('ج.م.')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('transport_cost')
                    ->label('تكلفة النقل')
                    ->numeric()
                    ->suffix('ج.م.')
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $subtotal = (float) ($get('subtotal') ?? 0);
                        $transportCost = (float) ($state ?? 0);
                        $taxAmount = (float) ($get('tax_amount') ?? 0);
                        $discountAmount = (float) ($get('discount_amount') ?? 0);
                        $netAmount = $subtotal + $transportCost + $taxAmount - $discountAmount;
                        $set('net_amount', $netAmount);
                    }),

                TextInput::make('tax_amount')
                    ->label('الضريبة')
                    ->numeric()
                    ->suffix('ج.م.')
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $subtotal = (float) ($get('subtotal') ?? 0);
                        $transportCost = (float) ($get('transport_cost') ?? 0);
                        $taxAmount = (float) ($state ?? 0);
                        $discountAmount = (float) ($get('discount_amount') ?? 0);
                        $netAmount = $subtotal + $transportCost + $taxAmount - $discountAmount;
                        $set('net_amount', $netAmount);
                    }),

                TextInput::make('discount_amount')
                    ->label('الخصم')
                    ->numeric()
                    ->suffix('ج.م.')
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $subtotal = (float) ($get('subtotal') ?? 0);
                        $transportCost = (float) ($get('transport_cost') ?? 0);
                        $taxAmount = (float) ($get('tax_amount') ?? 0);
                        $discountAmount = (float) ($state ?? 0);
                        $netAmount = $subtotal + $transportCost + $taxAmount - $discountAmount;
                        $set('net_amount', $netAmount);
                    }),

                TextInput::make('net_amount')
                    ->label('المبلغ النهائي')
                    ->numeric()
                    ->suffix('ج.م.')
                    ->disabled()
                    ->dehydrated(),

                Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'partial' => 'جزئي',
                        'paid' => 'مدفوع',
                    ])
                    ->default('pending'),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
