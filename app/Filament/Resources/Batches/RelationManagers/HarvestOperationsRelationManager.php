<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HarvestOperationsRelationManager extends RelationManager
{
    protected static string $relationship = 'harvestOperations';

    protected static ?string $title = 'عمليات الحصاد';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('operation_number')
                    ->label('رقم العملية')
                    ->default(fn () => 'HOP-'.str_pad(((\App\Models\HarvestOperation::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255),

                \Filament\Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options(\App\Enums\HarvestOperationStatus::class)
                    ->required()
                    ->native(false),

                \Filament\Forms\Components\DatePicker::make('start_date')
                    ->label('تاريخ البدء')
                    ->required()
                    ->default(now()),

                \Filament\Forms\Components\DatePicker::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('operation_number')
            ->columns([
                TextColumn::make('operation_number')
                    ->label('رقم العملية')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('مستمر'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['farm_id'] = $this->getOwnerRecord()->farm_id;
                        $data['created_by'] = auth('web')->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }
}
