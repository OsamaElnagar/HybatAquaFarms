<?php

namespace App\Filament\Resources\SalesOrders\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'harvestBoxes';

    protected static ?string $title = 'الأصناف (الصناديق)';

    protected static ?string $recordTitleAttribute = 'box_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('box_number')
                    ->label('رقم الصندوق')
                    ->sortable(),

                TextColumn::make('harvest.harvest_number')
                    ->label('رقم الحصاد')
                    ->sortable(),

                TextColumn::make('species.name')
                    ->label('النوع')
                    ->sortable(),

                TextColumn::make('classification')
                    ->label('التصنيف')
                    ->badge(),

                TextColumn::make('weight')
                    ->label('الوزن')
                    ->suffix(' كجم')
                    ->numeric(decimalPlaces: 2)
                    ->summarize(Sum::make()->label('الإجمالي')),

                TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->money('EGP'),

                TextColumn::make('subtotal')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->weight('bold')
                    ->summarize(Sum::make()->label('الإجمالي')),
            ])
            ->headerActions([
                // Maybe allow adding boxes manually if needed?
                // For now, assume adding is done via Harvest Operations.
            ])
            ->actions([
                Action::make('remove_item')
                    ->label('إزالة')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'sales_order_id' => null,
                            'trader_id' => null,
                            'is_sold' => false,
                            'sold_at' => null,
                            'subtotal' => null,
                            'unit_price' => null,
                        ]);
                        
                        $this->getOwnerRecord()->recalculateTotals();
                        
                        Notification::make()->title('تم إزالة الصندوق من الطلب')->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('remove_items')
                        ->label('إزالة المحدد')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'sales_order_id' => null,
                                    'trader_id' => null,
                                    'is_sold' => false,
                                    'sold_at' => null,
                                    'subtotal' => null,
                                    'unit_price' => null,
                                ]);
                            }
                            
                            $this->getOwnerRecord()->recalculateTotals();
                            
                            Notification::make()->title('تم إزالة الصناديق المحددة')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                        
                    BulkAction::make('update_price')
                        ->label('تعديل السعر')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            TextInput::make('unit_price')
                                ->label('السعر الجديد')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                $record->unit_price = $data['unit_price'];
                                $record->calculateSubtotal();
                                $record->save();
                            }
                            
                            $this->getOwnerRecord()->recalculateTotals();
                            
                            Notification::make()->title('تم تحديث الأسعار')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
