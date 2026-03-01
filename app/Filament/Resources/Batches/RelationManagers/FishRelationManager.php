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

class FishRelationManager extends RelationManager
{
    protected static string $relationship = 'fish';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'أنواع المزروعات في الدفعة';
    }

    public static function getModelLabel(): string
    {
        return 'نوع مزروعات';
    }

    public static function getPluralModelLabel(): string
    {
        return 'أنواع مزروعات';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Grid::make(2)
                    ->schema([
                        \Filament\Forms\Components\Select::make('species_id')
                            ->label('نوع المزروعات')
                            ->relationship('species', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        \Filament\Forms\Components\Select::make('factory_id')
                            ->label('المورد/المفرخ')
                            ->relationship('factory', 'name', function (\Illuminate\Database\Eloquent\Builder $query) {
                                return $query->where('type', '!=', \App\Enums\FactoryType::FEEDS);
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ]),

                \Filament\Schemas\Components\Grid::make(3)
                    ->schema([
                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $unitCost = $get('unit_cost');
                                if ($unitCost && $state) {
                                    $set('total_cost', (float) $unitCost * (int) $state);
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('unit_cost')
                            ->label('تكلفة الوحدة')
                            ->numeric(2)
                            ->suffix(' EGP ')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $quantity = $get('quantity');
                                if ($state && $quantity) {
                                    $set('total_cost', (float) $state * (int) $quantity);
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('total_cost')
                            ->label('التكلفة الإجمالية')
                            ->numeric()
                            ->suffix(' EGP ')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ]),

                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('species.name')
            ->columns([
                TextColumn::make('species.name')
                    ->label('النوع')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('factory.name')
                    ->label('المورد')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('تكلفة الوحدة')
                    ->money('EGP', decimalPlaces: 2, locale: 'en')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('التكلفة الإجمالية')
                    ->money('EGP', decimalPlaces: 0, locale: 'en')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('الإجمالي')->money('EGP', decimalPlaces: 0, locale: 'en')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
