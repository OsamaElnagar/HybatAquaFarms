<?php

namespace App\Filament\Resources\Factories\RelationManagers;

use App\Enums\PaymentMethod;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatchPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'batchPayments';

    protected static ?string $title = 'مدفوعات مفرخ الزريعة';

    protected static ?string $label = 'دفعة';

    protected static ?string $pluralLabel = 'مدفوعات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('batch_fish_id')
                    ->label('الدفعة (الصنف)')
                    ->options(function ($livewire) {
                        return \App\Models\BatchFish::with(['batch', 'species'])
                            ->where('factory_id', $livewire->ownerRecord->id)
                            ->get()
                            ->mapWithKeys(function ($fish) {
                                return [$fish->id => "{$fish->batch->batch_code} - {$fish->species->name}"];
                            });
                    })
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $fish = \App\Models\BatchFish::find($state);
                            if ($fish) {
                                $set('batch_id', $fish->batch_id);
                            }
                        }
                    })
                    ->required(),
                \Filament\Forms\Components\Hidden::make('batch_id'),
                \Filament\Forms\Components\Hidden::make('factory_id')
                    ->default(fn ($livewire) => $livewire->ownerRecord->id),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->default(now())
                    ->required(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric(),
                Select::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options(PaymentMethod::class)
                    ->default(PaymentMethod::CASH)
                    ->required(),
                TextInput::make('reference_number')
                    ->label('الرقم المرجعي'),
                Textarea::make('description')
                    ->label('البيان')
                    ->columnSpanFull(),
                \Filament\Forms\Components\Hidden::make('recorded_by')
                    ->default(fn () => auth()->id()),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('batchFish.batch.batch_code')
                    ->label('الدفعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batchFish.species.name')
                    ->label('الصنف')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->searchable(),
                TextColumn::make('reference_number')
                    ->label('الرقم المرجعي')
                    ->searchable(),
                TextColumn::make('recordedBy.name')
                    ->label('بواسطة')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التعديل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
