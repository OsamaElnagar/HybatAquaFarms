<?php

namespace App\Filament\Resources\Factories\Tables;

use App\Enums\FactoryType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FactoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('نوع المصنع')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('phone2')
                    ->label('الهاتف 2')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('contact_person')
                    ->label('اسم جهة الاتصال')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('payment_terms_days')
                    ->label('شروط الدفع (أيام)')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
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
                SelectFilter::make('type')
                    ->label('نوع المصنع')
                    ->options(FactoryType::class),
            ])
            ->recordActions([
                Action::make('call')
                    ->label('اتصال')
                    ->icon('heroicon-m-phone')
                    ->url(fn ($record) => $record->phone ? 'tel:'.$record->phone : null)
                    ->hidden(fn ($record) => blank($record->phone)),
                Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn ($record) => $record->phone ? 'https://wa.me/'.preg_replace('/\D+/', '', $record->phone) : null)
                    ->openUrlInNewTab()
                    ->hidden(fn ($record) => blank($record->phone)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
