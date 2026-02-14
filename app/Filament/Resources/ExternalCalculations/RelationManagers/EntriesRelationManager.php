<?php

namespace App\Filament\Resources\ExternalCalculations\RelationManagers;

use App\Enums\AccountType;
use App\Enums\ExternalCalculationType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'entries';

    protected static ?string $title = 'Transactions';

    protected static ?string $modelLabel = 'معاملة';

    protected static ?string $pluralModelLabel = 'المعاملات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name'),
                Forms\Components\DatePicker::make('date')
                    ->displayFormat('Y-m-d')
                    ->native(false)
                    ->label('التاريخ')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('النوع')
                    ->options(ExternalCalculationType::class)
                    ->required()
                    ->live(),
                Forms\Components\Select::make('treasury_account_id')
                    ->label('الخزنة / البنك')
                    ->relationship(
                        'treasuryAccount',
                        'name',
                        fn($query) => $query->where('is_treasury', true)
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('account_id')
                    ->label('الحساب المقابل')
                    ->relationship('account', 'name', function ($query, Get $get) {
                        $type = $get('type');
                        if ($type === ExternalCalculationType::Payment) {
                            $query->where('type', AccountType::Expense);
                        } elseif ($type === ExternalCalculationType::Receipt) {
                            $query->where('type', AccountType::Income);
                        } else {
                            $query->whereNull('id');
                        }

                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('reference_number')
                    ->label('رقم المرجع'),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth('web')->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('بيان')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state?->getLabel())
                    ->color(fn($state) => $state?->getColor()),
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('treasuryAccount.name')
                    ->label('الخزينة')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('account.name')
                    ->label('الحساب المقابل')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn($record) => $record->type === ExternalCalculationType::Receipt ? 'success' : 'danger')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Summarizer::make()
                            ->label('المقبوضات (دائن)')
                            ->query(fn($query) => $query->where('type', ExternalCalculationType::Receipt))
                            ->using(fn($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Tables\Columns\Summarizers\Summarizer::make()
                            ->label('المدفوعات (مدين)')
                            ->query(fn($query) => $query->where('type', ExternalCalculationType::Payment))
                            ->using(fn($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Tables\Columns\Summarizers\Summarizer::make()
                            ->label('صافي الرصيد')
                            ->using(fn($query) => $query->sum(DB::raw("CASE WHEN type = 'receipt' THEN amount ELSE -amount END")))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('المرجع')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options(ExternalCalculationType::class),
                Filter::make('date')
                    ->label('التاريخ')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('من تاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('إلى تاريخ')
                            ->displayFormat('Y-m-d')
                            ->native(false),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder => $query
                            ->when($data['from'] ?? null, fn(Builder $q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['to'] ?? null, fn(Builder $q, $date) => $q->whereDate('date', '<=', $date)),
                    ),
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
            ])->defaultSort('date', 'desc');
    }
}
