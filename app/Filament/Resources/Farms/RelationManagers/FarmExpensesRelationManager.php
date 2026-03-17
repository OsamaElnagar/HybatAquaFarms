<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Enums\AccountType;
use App\Enums\FarmExpenseType;
use App\Models\Batch;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FarmExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'farmExpenses';

    protected static ?string $title = 'مصروفات المزرعة';

    protected static ?string $modelLabel = 'مصروف';

    protected static ?string $pluralModelLabel = 'مصروفات';

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
                            ->displayFormat('Y-m-d')
                            ->default(now()),
                        Select::make('type')
                            ->label('النوع')
                            ->options(FarmExpenseType::class)
                            ->required()
                            ->live(),
                        Select::make('expense_category_id')
                            ->label('تصنيف المصروف')
                            ->relationship('expenseCategory', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('batch_id')
                            ->label('الدورة (اختياري)')
                            ->options(fn () => Batch::query()
                                ->where('farm_id', $this->getOwnerRecord()->getKey())
                                ->where('status', 'active')
                                ->pluck('batch_code', 'id'))
                            ->searchable()
                            ->placeholder('مصروف عام للمزرعة'),
                        Select::make('treasury_account_id')
                            ->label('الخزنة / البنك')
                            ->relationship(
                                'treasuryAccount',
                                'name',
                                fn ($query) => $query->where('is_treasury', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('account_id')
                            ->label('الحساب المقابل')
                            ->relationship('account', 'name', function ($query, Get $get) {
                                $type = $get('type');
                                if ($type === FarmExpenseType::Expense->value || $type === FarmExpenseType::Expense) {
                                    $query->where('type', AccountType::Expense);
                                } elseif ($type === FarmExpenseType::Revenue->value || $type === FarmExpenseType::Revenue) {
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
                Hidden::make('created_by')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date')
            ->modifyQueryUsing(fn ($query) => $query->with(['expenseCategory', 'treasuryAccount', 'account', 'batch']))
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('expenseCategory.name')
                    ->label('التصنيف')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('treasuryAccount.name')
                    ->label('الخزنة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('account.name')
                    ->label('الحساب المقابل')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('batch.name')
                    ->label('الدورة')
                    ->placeholder('عام')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn ($record) => $record->type === FarmExpenseType::Revenue ? 'success' : 'danger')
                    ->sortable()
                    ->summarize([
                        Summarizer::make()
                            ->label('إجمالي المصروفات')
                            ->query(fn ($query) => $query->where('type', FarmExpenseType::Expense))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Summarizer::make()
                            ->label('إجمالي الإيرادات')
                            ->query(fn ($query) => $query->where('type', FarmExpenseType::Revenue))
                            ->using(fn ($query) => $query->sum('amount'))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                        Summarizer::make()
                            ->label('الصافي')
                            ->using(fn ($query) => $query->sum(DB::raw("CASE WHEN type = 'revenue' THEN amount ELSE -amount END")))
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
                TextColumn::make('reference_number')
                    ->label('المرجع')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(FarmExpenseType::class),
                SelectFilter::make('expense_category_id')
                    ->label('التصنيف')
                    ->relationship('expenseCategory', 'name'),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_from')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->label('من تاريخ'),
                        DatePicker::make('date_to')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'] ?? null, fn ($q, $date) => $q->where('date', '>=', Carbon::parse($date)))
                            ->when($data['date_to'] ?? null, fn ($q, $date) => $q->where('date', '<=', Carbon::parse($date)));
                    }),
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
            ])
            ->defaultSort('date', 'desc');
    }
}
