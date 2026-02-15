<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Enums\ExternalCalculationType;
use App\Models\Account;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExternalCalculationsRelationManager extends RelationManager
{
    protected static string $relationship = 'externalCalculations';

    protected static ?string $title = 'حسابات خارجية';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->native(false)
                            ->displayFormat('y-m-d')
                            ->default(now()),
                        Select::make('type')
                            ->label('النوع')
                            ->options(ExternalCalculationType::class)
                            ->required(),
                        Select::make('treasury_account_id')
                            ->label('الخزينة')
                            ->options(fn () => Account::where('is_treasury', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('account_id')
                            ->label('الحساب المقابل')
                            ->options(fn () => Account::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('reference_number')
                            ->label('رقم المرجع')
                            ->maxLength(255),
                    ]),
                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
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

                TextColumn::make('description')
                    ->label('الوصف')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('treasuryAccount.name')
                    ->label('الخزينة')
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('الحساب المقابل')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(ExternalCalculationType::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
