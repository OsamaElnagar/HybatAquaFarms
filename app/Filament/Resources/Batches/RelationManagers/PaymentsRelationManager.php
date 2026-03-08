<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Enums\PaymentMethod;
use App\Filament\Resources\BatchPayments\Schemas\BatchPaymentForm;
use App\Filament\Resources\BatchPayments\Tables\BatchPaymentsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
        return BatchPaymentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return BatchPaymentsTable::configure($table)
            // $table
            //     ->recordTitleAttribute('amount')
            //     ->columns([
            //         TextColumn::make('date')
            //             ->label('تاريخ الدفعة')
            //             ->date('Y-m-d')
            //             ->sortable(),
            //         TextColumn::make('amount')
            //             ->label('المبلغ')
            //             ->money('EGP', locale: 'en', decimalPlaces: 0)
            //             ->color('success')
            //             ->sortable()
            //             ->summarize([
            //                 \Filament\Tables\Columns\Summarizers\Sum::make()
            //                     ->label('إجمالي المدفوع')
            //                     ->money('EGP', locale: 'en', decimalPlaces: 0),
            //                 \Filament\Tables\Columns\Summarizers\Count::make()
            //                     ->label('عدد الدفعات'),
            //             ]),
            //         TextColumn::make('payment_method')
            //             ->label('طريقة الدفع')
            //             ->badge()
            //             ->searchable()
            //             ->sortable(),
            //         TextColumn::make('reference_number')
            //             ->label('رقم المرجع')
            //             ->searchable()
            //             ->toggleable(),
            //         TextColumn::make('description')
            //             ->label('الوصف')
            //             ->limit(50)
            //             ->wrap()
            //             ->toggleable(),
            //         TextColumn::make('recordedBy.name')
            //             ->label('سجل بواسطة')
            //             ->toggleable(),
            //         TextColumn::make('created_at')
            //             ->label('تاريخ الإنشاء')
            //             ->dateTime()
            //             ->sortable()
            //             ->toggleable(isToggledHiddenByDefault: true),
            //     ])
            //     ->filters([
            //         \Filament\Tables\Filters\SelectFilter::make('payment_method')
            //             ->label('طريقة الدفع')
            //             ->options(PaymentMethod::class),
            //     ])
            //     ->defaultSort('date', 'desc')
            ->headerActions([
                CreateAction::make()->label('إضافة دفعة مالية')
                    ->mutateDataUsing(function (array $data, $livewire): array {
                        $data['batch_id'] = $livewire->ownerRecord->id;
                        $data['recorded_by'] = auth('web')->id();
                        // Set factory_id from batch if not set
                        if (empty($data['factory_id']) && $livewire->ownerRecord->factory_id) {
                            $data['factory_id'] = $livewire->ownerRecord->factory_id;
                        }

                        return $data;
                    }),
            ]);
        //     ->recordActions([
        //         EditAction::make(),
        //     ])
        //     ->toolbarActions([
        //         BulkActionGroup::make([
        //             DeleteBulkAction::make(),
        //         ]),
        //     ]);
    }
}
