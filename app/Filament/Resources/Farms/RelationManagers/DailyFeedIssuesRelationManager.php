<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Filament\Resources\DailyFeedIssues\Schemas\DailyFeedIssueForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['unit', 'feedItem', 'batch', 'warehouse', 'recordedBy']))
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('feedItem.name')
                    ->label('صنف العلف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->suffix(' كجم')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label('المجموع')
                            ->numeric()
                            ->suffix(' كجم'),
                    ]),
                TextColumn::make('batch.batch_code')
                    ->label('الدفعة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('من تاريخ'),
                        DatePicker::make('to')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date) => $query->where('date', '>=', $date))
                            ->when($data['to'], fn (Builder $query, $date) => $query->where('date', '<=', $date));
                    }),
                SelectFilter::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'code'),
                SelectFilter::make('feed_item_id')
                    ->label('صنف العلف')
                    ->relationship('feedItem', 'name'),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة صرف علف')
                    ->mutateDataUsing(function (array $data): array {
                        $data['farm_id'] = $this->getOwnerRecord()->id;
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
