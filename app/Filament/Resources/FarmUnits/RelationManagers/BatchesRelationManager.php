<?php

namespace App\Filament\Resources\FarmUnits\RelationManagers;

use App\Enums\BatchStatus;
use App\Filament\Resources\Batches\Schemas\BatchForm;
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

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'دفعات الزريعة';

    public function form(Schema $schema): Schema
    {
        return BatchForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_code')
            ->columns([
                TextColumn::make('batch_code')
                    ->label('كود الدفعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('species.name')
                    ->label('نوع المزروع')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof BatchStatus ? $state->getLabel() : $state)
                    ->color(fn ($state) => $state instanceof BatchStatus ? $state->getColor() : 'gray')
                    ->sortable(),
                TextColumn::make('entry_date')
                    ->label('تاريخ الإدخال')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('initial_quantity')
                    ->label('الكمية الأولية')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_quantity')
                    ->label('الكمية الحالية')
                    ->numeric()
                    ->color(fn ($record) => $record->current_quantity < $record->initial_quantity ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('current_weight_avg')
                    ->label('متوسط الوزن (جم)')
                    ->numeric(decimalPlaces: 3)
                    ->suffix(' جم')
                    ->toggleable(),
                TextColumn::make('total_cost')
                    ->label('التكلفة الإجمالية')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(BatchStatus::class)
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['unit_id'] = $this->getOwnerRecord()->id;
                        $data['farm_id'] = $this->getOwnerRecord()->farm_id;

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
            ->defaultSort('entry_date', 'desc');
    }
}
