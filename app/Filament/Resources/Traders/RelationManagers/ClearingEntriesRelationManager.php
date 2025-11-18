<?php

namespace App\Filament\Resources\Traders\RelationManagers;

use App\Filament\Resources\ClearingEntries\Schemas\ClearingEntryForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClearingEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'clearingEntries';

    protected static ?string $title = 'تسويات الحساب';

    public function form(Schema $schema): Schema
    {
        return ClearingEntryForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('clearing_number')
            ->columns([
                TextColumn::make('clearing_number')
                    ->label('رقم التسوية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->numeric(decimalPlaces: 2)
                            ->prefix('ج.م '),
                    ]),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة تسوية حساب')
                    ->mutateDataUsing(function (array $data): array {
                        $data['trader_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
