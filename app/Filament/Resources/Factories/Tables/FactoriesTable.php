<?php

namespace App\Filament\Resources\Factories\Tables;

use App\Enums\FactoryType;
use App\Filament\Resources\Factories\FactoryResource;
use App\Models\Factory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FactoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('نوع المصنع')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('phone2')
                    ->label('الهاتف 2')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('contact_person')
                    ->label('اسم جهة الاتصال')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('payment_terms_days')
                    ->label('شروط الدفع (أيام)')
                    ->numeric(locale: 'en')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('current_year_activity')
                    ->label('نشاط العام الحالي')
                    ->state(function (Factory $record): string {
                        if ($record->type === FactoryType::SUPPLIER) {
                            return '-';
                        }

                        $activity = $record->current_year_activity;
                        $purchases = number_format($activity['purchases']);
                        $payments = number_format($activity['payments']);

                        return "<div class='text-sm space-y-1'>
                                    <div><span class='font-medium text-gray-500'>مشتريات:</span> <span class='text-danger-600 font-semibold'>{$purchases} EGP</span></div>
                                    <div><span class='font-medium text-gray-500'>مدفوعات:</span> <span class='text-success-600 font-semibold'>{$payments} EGP</span></div>
                                </div>";
                    })
                    ->html()
                    ->toggleable(),
                TextColumn::make('past_year_activity')
                    ->label('نشاط العام الماضي')
                    ->state(function (Factory $record): string {
                        if ($record->type === FactoryType::SUPPLIER) {
                            return '-';
                        }

                        $activity = $record->past_year_activity;
                        $purchases = number_format($activity['purchases']);
                        $payments = number_format($activity['payments']);

                        return "<div class='text-sm space-y-1'>
                                    <div><span class='font-medium text-gray-500'>مشتريات:</span> <span class='text-danger-600 font-semibold'>{$purchases} EGP</span></div>
                                    <div><span class='font-medium text-gray-500'>مدفوعات:</span> <span class='text-success-600 font-semibold'>{$payments} EGP</span></div>
                                </div>";
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع المصنع')
                    ->options(FactoryType::class),
            ])
            ->recordActions([
                Action::make('call')
                    ->label('اتصال')
                    ->icon('heroicon-m-phone')
                    ->url(fn ($record) => $record->phone ? 'tel:'.$record->phone : null)
                    ->hidden(fn ($record) => blank($record->phone)),
                Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        if (blank($record->phone)) {
                            return null;
                        }

                        $phone = preg_replace('/\D+/', '', $record->phone);

                        if (! str_starts_with($phone, '2')) {
                            $phone = '2'.$phone;
                        }

                        return 'https://wa.me/'.$phone;
                    })
                    ->openUrlInNewTab()
                    ->hidden(fn ($record) => blank($record->phone)),
                Action::make('statement')
                    ->label('كشف الحساب')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => FactoryResource::getUrl('statement', ['record' => $record])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
