<?php

namespace App\Filament\Resources\PettyCashes\RelationManagers;

use App\Enums\PettyTransacionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'معاملات العهدة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('direction')
                    ->label('النوع')
                    ->options([
                        'out' => 'صرف (مصروف)',
                        'in' => 'قبض (تزويد)',
                    ])
                    ->required()
                    ->live()
                    ->default('out'),
                Select::make('expense_category_id')
                    ->label('نوع المصروف')
                    ->relationship('expenseCategory', 'name', fn ($query) => $query->where('is_active', true))
                    ->visible(fn ($get) => $get('direction') === 'out')
                    ->required(fn ($get) => $get('direction') === 'out')
                    ->searchable()
                    ->preload(),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->suffix(' EGP ')
                    ->minValue(0.01)
                    ->step(0.01),
                Textarea::make('description')
                    ->label('الوصف التفصيلي')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(1000)
                    ->helperText('اكتب تفاصيل المصروف/التزويد بالتفصيل'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('direction')
                    ->label('النوع')
                    ->badge()

                    ->sortable(),
                TextColumn::make('expenseCategory.name')
                    ->label('نوع المصروف')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->suffix(' EGP ')
                    ->color(fn ($record) => $record->direction === 'out' ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('voucher.voucher_number')
                    ->label('رقم السند')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->label('النوع')
                    ->options(PettyTransacionType::class),
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->label('نوع المصروف')
                    ->relationship('expenseCategory', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة معاملة')
                    ->mutateDataUsing(function (array $data): array {
                        $data['recorded_by'] = Auth::id();
                        $data['petty_cash_id'] = $this->getOwnerRecord()->id;

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
