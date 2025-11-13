<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'batchPayments';

    protected static ?string $title = 'مدفوعات الدفعة';

    protected static ?string $label = 'دفعة';

    protected static ?string $pluralLabel = 'مدفوعات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('تفاصيل الدفعة')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Select::make('factory_id')
                                    ->label('المورد')
                                    ->relationship('factory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->default(fn ($livewire) => $livewire->getOwnerRecord()?->factory_id),
                                DatePicker::make('date')
                                    ->label('تاريخ الدفعة')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('Y-m-d')
                                    ->native(false),
                            ]),
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('المبلغ')
                                    ->required()
                                    ->numeric()
                                    ->prefix('ج.م ')
                                    ->minValue(0.01)
                                    ->step(0.01),
                                Select::make('payment_method')
                                    ->label('طريقة الدفع')
                                    ->options([
                                        'cash' => 'نقدي',
                                        'bank' => 'تحويل بنكي',
                                        'check' => 'شيك',
                                    ])
                                    ->searchable(),
                            ]),
                        TextInput::make('reference_number')
                            ->label('رقم المرجع')
                            ->maxLength(255)
                            ->helperText('رقم الشيك أو التحويل البنكي')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                \Filament\Schemas\Components\Section::make('ملاحظات')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('date')
                    ->label('تاريخ الدفعة')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م ')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'نقدي',
                        'bank' => 'تحويل بنكي',
                        'check' => 'شيك',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'bank' => 'info',
                        'check' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('reference_number')
                    ->label('رقم المرجع')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => 'نقدي',
                        'bank' => 'تحويل بنكي',
                        'check' => 'شيك',
                    ]),
            ])
            ->defaultSort('date', 'desc')
            ->summary([
                \Filament\Tables\Columns\Summarizers\Sum::make('amount')
                    ->label('إجمالي المدفوع')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('ج.م '),
                \Filament\Tables\Columns\Summarizers\Count::make('amount')
                    ->label('عدد الدفعات'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, $livewire): array {
                        $data['batch_id'] = $livewire->ownerRecord->id;
                        $data['recorded_by'] = auth()->id();
                        // Set factory_id from batch if not set
                        if (empty($data['factory_id']) && $livewire->ownerRecord->factory_id) {
                            $data['factory_id'] = $livewire->ownerRecord->factory_id;
                        }

                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
