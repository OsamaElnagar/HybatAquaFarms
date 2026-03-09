<?php

namespace App\Filament\Resources\Factories\RelationManagers;

use App\Enums\PaymentMethod;
use App\Enums\RepaymentType;
use App\Models\PartnerLoan;
use App\Models\PartnerLoanRepayment;
use Filament\Actions\Action;
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
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PartnerLoansRelationManager extends RelationManager
{
    protected static string $relationship = 'partnerLoans';

    protected static ?string $title = 'سلف الشريك';

    protected static ?string $modelLabel = 'سلفة';

    protected static ?string $pluralModelLabel = 'سلف';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now())
                    ->displayFormat('Y-m-d')
                    ->native(false),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->suffix('EGP'),
                Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::CASH)
                    ->required(),
                Select::make('treasury_account_id')
                    ->label('الخزنة / البنك')
                    ->relationship(
                        'treasuryAccount',
                        'name',
                        fn($query) => $query->where('is_treasury', true)
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                Hidden::make('created_by')
                    ->default(fn() => auth('web')->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('مبلغ السلفة')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->label('المجموع')
                            ->money('EGP', locale: 'en', decimalPlaces: 0),
                    ]),
                TextColumn::make('repayments_sum_amount')
                    ->label('المسدد')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->sortable()
                    ->color('success'),
                TextColumn::make('remaining_balance')
                    ->label('المتبقي')
                    ->money('EGP', locale: 'en', decimalPlaces: 0)
                    ->color(fn($state) => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withSum('repayments', 'amount'))
            ->filters([])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('repay')
                    ->label('سداد')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn(PartnerLoan $record) => $record->remaining_balance > 0)
                    ->schema([
                        Select::make('repayment_type')
                            ->label('نوع السداد')
                            ->options(RepaymentType::class)
                            ->required()
                            ->live()
                            ->default(RepaymentType::Cash),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false),
                        TextInput::make('amount')
                            ->label('المبلغ')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->suffix('EGP'),
                        Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options(PaymentMethod::class)
                            ->visible(fn(Get $get): bool => $get('repayment_type') === RepaymentType::Cash)
                            ->required(fn(Get $get): bool => $get('repayment_type') === RepaymentType::Cash),
                        Select::make('treasury_account_id')
                            ->label('الخزنة / البنك')
                            ->relationship(
                                'treasuryAccount',
                                'name',
                                fn($query) => $query->where('is_treasury', true)
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn(Get $get): bool => $get('repayment_type') === RepaymentType::Cash)
                            ->required(fn(Get $get): bool => $get('repayment_type') === RepaymentType::Cash),
                        Textarea::make('description')
                            ->label('الوصف'),
                    ])
                    ->action(function (PartnerLoan $record, array $data): void {
                        $remaining = $record->remaining_balance;
                        if ((float) $data['amount'] > $remaining) {
                            Notification::make()
                                ->title('خطأ')
                                ->body("المبلغ ({$data['amount']}) أكبر من المتبقي ({$remaining})")
                                ->danger()
                                ->send();

                            return;
                        }

                        PartnerLoanRepayment::create([
                            'partner_loan_id' => $record->id,
                            'repayment_type' => $data['repayment_type'],
                            'date' => $data['date'],
                            'amount' => $data['amount'],
                            'payment_method' => $data['payment_method'] ?? null,
                            'treasury_account_id' => $data['treasury_account_id'] ?? null,
                            'description' => $data['description'] ?? null,
                            'created_by' => auth('web')->id(),
                        ]);

                        Notification::make()
                            ->title('تم السداد بنجاح')
                            ->success()
                            ->send();
                    }),
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
