<?php

namespace App\Filament\Resources\ExternalCalculations\RelationManagers;

use App\Enums\ExternalCalculationStatementStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatementsRelationManager extends RelationManager
{
    protected static string $relationship = 'statements';

    protected static ?string $title = 'كشوف حسابات';

    protected static ?string $modelLabel = 'كشف حساب';

    protected static ?string $pluralModelLabel = 'كشوف حسابات';

    // public function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             TextInput::make('title')
    //                 ->required()
    //                 ->maxLength(255),
    //         ]);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان الكشف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('تاريخ الفتح')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('تاريخ الإغلاق')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('لا تزال مفتوحة'),
                TextColumn::make('opening_balance')
                    ->label('رصيد افتتاحي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('total_credits')
                    ->label('إجمالي المقبوضات')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('success'),
                TextColumn::make('total_debits')
                    ->label('إجمالي المدفوعات')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color('danger'),
                TextColumn::make('net_balance')
                    ->label('الرصيد الختامي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(ExternalCalculationStatementStatus::class),
            ])
            ->headerActions([
                // Usually handled by the main resource
            ])
            ->recordActions([
                // EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('opened_at', 'desc');
    }
}
