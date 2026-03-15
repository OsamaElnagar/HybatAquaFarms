<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Enums\FactoryType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FishRelationManager extends RelationManager
{
    protected static string $relationship = 'fish';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
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
                Grid::make(2)
                    ->schema([
                        Select::make('species_id')
                            ->label('نوع المزروعات')
                            ->relationship('species', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Select::make('factory_id')
                            ->label('المورد/المفرخ')
                            ->relationship('factory', 'name', function (Builder $query) {
                                return $query->where('type', '!=', FactoryType::FEEDS);
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ]),

                Grid::make(3)
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

                Textarea::make('notes')
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
                    ->sortable()
                    ->summarize(Sum::make()->label('الإجمالي')->numeric(decimalPlaces: 0, locale: 'en')),
                TextColumn::make('unit_cost')
                    ->label('تكلفة الوحدة')
                    ->money('EGP', decimalPlaces: 2, locale: 'en')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('التكلفة الإجمالية')
                    ->money('EGP', decimalPlaces: 0, locale: 'en')
                    ->sortable()
                    ->summarize(Sum::make()->label('الإجمالي')->money('EGP', decimalPlaces: 0, locale: 'en')),
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
