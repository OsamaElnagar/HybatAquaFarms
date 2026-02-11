<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Enums\FarmStatus;
use App\Enums\UnitType;
use App\Filament\Resources\FarmUnits\Schemas\FarmUnitForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'الوحدات والأحواض';

    public function form(Schema $schema): Schema
    {
        return FarmUnitForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('batches')->withSum('dailyFeedIssues as total_feed_consumed', 'quantity'))
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->copyable()
                    ->copyMessage('تم نسخ الكود')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->copyable()
                    ->copyMessage('تم نسخ الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit_type')
                    ->label('النوع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('capacity')
                    ->label('السعة')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('batches_count')
                    ->label('الدفعات')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('total_feed_consumed')
                    ->label('استهلاك العلف (كجم)')
                    ->numeric()
                    ->color('info')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('unit_type')
                    ->label('النوع')
                    ->options(UnitType::class)
                    ->native(false),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(FarmStatus::class)
                    ->native(false),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة حوض | وحدة')
                    ->mutateDataUsing(function (array $data): array {
                        $data['farm_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }
}
