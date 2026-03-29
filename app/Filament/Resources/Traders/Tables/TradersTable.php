<?php

namespace App\Filament\Resources\Traders\Tables;

use App\Filament\Resources\Traders\Actions\GiveCashAction;
use App\Filament\Resources\Traders\Actions\ReceivePaymentAction;
use App\Filament\Resources\Traders\TraderResource;
use App\Models\Trader;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TradersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->copyable()
                    ->copyMessage('تم نسخ الكود')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('outstanding_balance')
                    ->label('صافى الحساب')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn ($record) => $record->outstanding_balance > 0 ?
                        'warning' : ($record->outstanding_balance < 0 ? 'danger' : 'default'))
                    ->sortable(),
                TextColumn::make('sales_orders_count')
                    ->counts('salesOrders')
                    ->label('فواتير/مرات البيع')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('contact_person')
                    ->label('الشخص المسؤول')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('trader_type')
                    ->label('النوع')
                    ->badge()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('credit_limit')
                    ->label('حد الائتمان')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('trader_type')
                    ->label('نوع التاجر')
                    ->options([
                        'wholesale' => 'جملة',
                        'retail' => 'تجزئة',
                        'exporter' => 'مصدّر',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ReceivePaymentAction::make(),
                    GiveCashAction::make(),
                    Action::make('statementOfAccount')
                        ->label('كشف الحساب')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn (Trader $record): string => TraderResource::getUrl('statement', ['record' => $record])),
                    Action::make('statementsHistory')
                        ->label('سجل الكشوفات')
                        ->icon('heroicon-o-list-bullet')
                        ->color('gray')
                        ->url(fn (Trader $record): string => TraderResource::getUrl('statements', ['record' => $record])),
                    ViewAction::make()->label('عرض'),
                    EditAction::make()->label('تعديل'),
                ])->label('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Large)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('name');
    }
}
