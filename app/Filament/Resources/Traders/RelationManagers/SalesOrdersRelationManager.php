<?php

namespace App\Filament\Resources\Traders\RelationManagers;

use App\Enums\PaymentStatus;
use App\Filament\Resources\SalesOrders\Schemas\SalesOrderForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalesOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'salesOrders';

    protected static ?string $title = 'المبيعات';

    public function form(Schema $schema): Schema
    {
        return SalesOrderForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم العملية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('farm.name')
                    ->label('المزرعة')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentStatus ? $state->getLabel() : $state)
                    ->color(fn ($state) => $state instanceof PaymentStatus ? $state->getColor() : 'gray')
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->label('حالة التوصيل')
                    ->badge()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('تاريخ التوصيل')
                    ->date('Y-m-d')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options(PaymentStatus::class)
                    ->native(false),
                SelectFilter::make('delivery_status')
                    ->label('حالة التوصيل')
                    ->options([
                        'pending' => 'معلق',
                        'delivered' => 'تم التوصيل',
                        'cancelled' => 'ملغي',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['trader_id'] = $this->getOwnerRecord()->id;
                        $data['created_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
