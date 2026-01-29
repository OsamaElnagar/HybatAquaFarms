<?php

namespace App\Filament\Resources\FactoryPayments\Tables;

use App\Enums\FactoryType;
use App\Enums\PaymentMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FactoryPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('factory.name')
                    ->label('المصنع')
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference_number')
                    ->label('رقم المرجع')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('factory_id')
                    ->label('المصنع')
                    ->relationship('factory', 'name', function (Builder $query) {
                        return $query->where('type', FactoryType::FEEDS);
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(PaymentMethod::class)
                    ->native(),
            ])
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
