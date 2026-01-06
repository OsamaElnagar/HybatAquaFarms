<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'الإيصالات (أوردرات للحلقات)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('الكود')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('يتم إنشاؤه تلقائياً'),
                Select::make('harvest_id')
                    ->label('جلسة الحصاد')
                    ->relationship(
                        'harvest',
                        'harvest_number',
                        modifyQueryUsing: fn (Builder $query) => $query->where('harvest_operation_id', $this->getOwnerRecord()->id),
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->harvest_number.' - '.$record->harvest_date->format('Y-m-d'))
                    ->required(),
                Select::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->required(),
                Select::make('driver_id')
                    ->label('السائق')
                    ->relationship('driver', 'name'),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable(),
                TextColumn::make('harvest.harvest_number')
                    ->label('رقم الحصاد')
                    ->searchable(),
                TextColumn::make('trader.name')
                    ->label('التاجر')
                    ->searchable(),
                TextColumn::make('driver.name')
                    ->label('السائق')
                    ->searchable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
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
                //
            ])
            ->headerActions([
                CreateAction::make()->label('إنشاء إيصال'),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل إيصال'),
                // DissociateAction::make(),
                DeleteAction::make()->label('حذف إيصال'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make()->label('حذف إيصالات'),
                ]),
            ]);
    }
}
