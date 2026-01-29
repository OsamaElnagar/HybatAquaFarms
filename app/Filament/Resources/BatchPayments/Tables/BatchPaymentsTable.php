<?php

namespace App\Filament\Resources\BatchPayments\Tables;

use App\Enums\FactoryType;
use App\Enums\PaymentMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BatchPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch.batch_code')
                    ->label('كود الدفعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('factory.name')
                    ->label('المورد')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('تاريخ الدفعة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->searchable(),
                // TextColumn::make('reference_number')
                //     ->label('رقم المرجع')
                //     ->searchable()
                //     ->toggleable(),
                // TextColumn::make('description')
                //     ->label('الوصف')
                //     ->limit(50)
                //     ->wrap()
                //     ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('batch_id')
                    ->label('الدفعة')
                    ->relationship('batch', 'batch_code', modifyQueryUsing: fn ($query) => $query->latest())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('factory_id')
                    ->label('المورد')
                    ->relationship('factory', 'name', function (Builder  $query) {
                        return $query->where('type', FactoryType::SEEDS);
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(PaymentMethod::class)
                    ->searchable()
                    ->native(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
