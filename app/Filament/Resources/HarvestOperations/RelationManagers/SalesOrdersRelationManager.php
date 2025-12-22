<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use App\Filament\Resources\SalesOrders\Schemas\SalesOrderForm;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'salesOrders';

    protected static ?string $title = 'المبيعات';

    protected static ?string $recordTitleAttribute = 'order_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الأمر')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('total_boxes')
                    ->label('الصناديق')
                    ->numeric(),

                TextColumn::make('total_weight')
                    ->label('الوزن (كجم)')
                    ->formatStateUsing(fn ($state) => number_format($state)),

                TextColumn::make('boxes_subtotal')
                    ->label('مجموع الصناديق')
                    ->money('EGP')
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->money('EGP')),

                TextColumn::make('commission_rate')
                    ->label('العمولة %')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('commission_amount')
                    ->label('قيمة العمولة')
                    ->money('EGP')
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->money('EGP'))
                    ->toggleable(),

                TextColumn::make('transport_cost')
                    ->label('النقل')
                    ->money('EGP')
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->money('EGP'))
                    ->toggleable(),

                TextColumn::make('net_amount')
                    ->label('الصافي')
                    ->money('EGP')
                    ->sortable()
                    ->summarize(Sum::make()->label('المجموع')->money('EGP'))
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->sortable(),

                TextColumn::make('delivery_status')
                    ->label('حالة التسليم')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'partial' => 'جزئي',
                        'paid' => 'مدفوع',
                    ])
                    ->native(false),

                SelectFilter::make('delivery_status')
                    ->label('حالة التسليم')
                    ->options([
                        'pending' => 'معلق',
                        'in_transit' => 'في الطريق',
                        'delivered' => 'تم التسليم',
                    ])
                    ->native(false),
            ])
            ->headerActions([
                // Create action can be added later
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }

    public function form(Schema $schema): Schema
    {
        return SalesOrderForm::configure($schema);
    }
}
