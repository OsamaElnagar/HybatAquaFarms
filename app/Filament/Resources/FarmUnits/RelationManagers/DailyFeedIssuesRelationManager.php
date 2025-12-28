<?php

namespace App\Filament\Resources\FarmUnits\RelationManagers;

use App\Filament\Resources\DailyFeedIssues\Schemas\DailyFeedIssueForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DailyFeedIssuesRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyFeedIssues';

    protected static ?string $title = 'صرف الأعلاف اليومي';

    public function form(Schema $schema): Schema
    {
        return DailyFeedIssueForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('feedItem.name')
                    ->label('صنف العلف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->suffix(' كجم')
                    ->sortable(),
                TextColumn::make('batch.batch_code')
                    ->label('الدفعة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة صرف علف')
                    ->mutateDataUsing(function (array $data): array {
                        $data['unit_id'] = $this->getOwnerRecord()->id;
                        $data['farm_id'] = $this->getOwnerRecord()->farm_id;
                        $data['recorded_by'] = Auth::id();

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
