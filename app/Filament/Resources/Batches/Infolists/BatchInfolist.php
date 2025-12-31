<?php

namespace App\Filament\Resources\Batches\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات أساسية')
                ->schema([
                    TextEntry::make('batch_code')
                        ->label('كود الدفعة')
                        ->columnSpan(1),
                    TextEntry::make('farm.name')
                        ->label('المزرعة')
                        ->columnSpan(1),
                    TextEntry::make('unit.code')
                        ->label('الوحدة')
                        ->placeholder('غير محدد')
                        ->columnSpan(1),
                    TextEntry::make('species.name')
                        ->label('النوع')
                        ->columnSpan(1),
                    TextEntry::make('factory.name')
                        ->label('مصنع التفريخ')
                        ->placeholder('غير محدد')
                        ->columnSpan(1),
                    TextEntry::make('entry_date')
                        ->label('تاريخ الإدخال')
                        ->date('Y-m-d')
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('الكميات والأوزان')
                ->schema([
                    TextEntry::make('initial_quantity')
                        ->label('الكمية الأولية')
                        ->badge()
                        ->color('primary')
                        ->columnSpan(1),
                    TextEntry::make('current_quantity')
                        ->label('الكمية الحالية')
                        ->badge()
                        ->color(
                            fn ($record) => $record->current_quantity <
                            $record->initial_quantity
                                ? 'warning'
                                : 'success',
                        )
                        ->columnSpan(1),
                    TextEntry::make('mortality_count')
                        ->label('عدد النفوق')
                        ->state(
                            fn ($record) => $record->initial_quantity -
                                $record->current_quantity,
                        )
                        ->badge()
                        ->color('danger')
                        ->columnSpan(1),
                    TextEntry::make('mortality_rate')
                        ->label('معدل النفوق')
                        ->state(function ($record) {
                            if (
                                ! $record->initial_quantity ||
                                $record->initial_quantity == 0
                            ) {
                                return '0%';
                            }
                            $mortality =
                                $record->initial_quantity -
                                $record->current_quantity;
                            $rate =
                                ($mortality / $record->initial_quantity) * 100;

                            return number_format($rate).'%';
                        })
                        ->badge()
                        ->color(function ($record) {
                            if (
                                ! $record->initial_quantity ||
                                $record->initial_quantity == 0
                            ) {
                                return 'gray';
                            }
                            $mortality =
                                $record->initial_quantity -
                                $record->current_quantity;
                            $rate =
                                ($mortality / $record->initial_quantity) * 100;

                            return $rate > 10
                                ? 'danger'
                                : ($rate > 5
                                    ? 'warning'
                                    : 'success');
                        })
                        ->columnSpan(1),
                    TextEntry::make('initial_weight_avg')
                        ->label('متوسط الوزن الأولي')
                        ->formatStateUsing(
                            fn ($state) => $state
                                ? number_format($state).' جم'
                                : 'غير محدد',
                        )
                        ->columnSpan(1),
                    TextEntry::make('current_weight_avg')
                        ->label('متوسط الوزن الحالي')
                        ->formatStateUsing(
                            fn ($state) => $state
                                ? number_format($state).' جم'
                                : 'غير محدد',
                        )
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('المصدر والحالة')
                ->schema([
                    TextEntry::make('source')
                        ->label('المصدر')
                        ->badge()
                        ->columnSpan(1),
                    TextEntry::make('status')
                        ->label('الحالة')
                        ->badge()
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('التكاليف والمدفوعات')
                ->schema([
                    TextEntry::make('unit_cost')
                        ->label('تكلفة الوحدة')
                        ->formatStateUsing(
                            fn ($state) => $state
                                ? number_format($state).' EGP '
                                : 'غير محدد',
                        )
                        ->columnSpan(1),
                    TextEntry::make('total_cost')
                        ->label('التكلفة الإجمالية')
                        ->formatStateUsing(
                            fn ($state) => $state
                                ? number_format($state).' EGP '
                                : 'غير محدد',
                        )
                        ->badge()
                        ->color('success')
                        ->columnSpan(1),
                    TextEntry::make('total_paid')
                        ->label('المدفوع')
                        ->formatStateUsing(
                            fn ($record) => $record->total_paid
                                ? number_format($record->total_paid).' EGP '
                                : '0.00 EGP',
                        )
                        ->badge()
                        ->color('info')
                        ->columnSpan(1),
                    TextEntry::make('outstanding_balance')
                        ->label('المتبقي')
                        ->formatStateUsing(
                            fn ($record) => $record->outstanding_balance
                                ? number_format(
                                    $record->outstanding_balance,
                                    2,
                                ).' EGP '
                                : '0.00 EGP',
                        )
                        ->badge()
                        ->color(
                            fn ($record) => $record->outstanding_balance > 0
                                ? 'warning'
                                : 'success',
                        )
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('الإحصائيات')
                ->schema([
                    TextEntry::make('harvest_operations_count')
                        ->label('عمليات الحصاد')
                        ->state(fn ($record) => $record->harvestOperations()->count())
                        ->badge()
                        ->color('primary')
                        ->columnSpan(1),
                    TextEntry::make('movements_count')
                        ->label('الحركات')
                        ->state(fn ($record) => $record->movements()->count())
                        ->badge()
                        ->color('info')
                        ->columnSpan(1),
                    TextEntry::make('payments_count')
                        ->label('المدفوعات')
                        ->state(
                            fn ($record) => $record->batchPayments()->count(),
                        )
                        ->badge()
                        ->color('success')
                        ->columnSpan(1),
                    TextEntry::make('days_since_entry')
                        ->label('عدد الأيام')
                        ->state(function ($record) {
                            if (! $record->entry_date) {
                                return '-';
                            }

                            return now()->diffInDays($record->entry_date).
                                ' يوم';
                        })
                        ->badge()
                        ->color('warning')
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('الربحية والتكاليف التشغيلية')
                ->schema([
                    TextEntry::make('total_feed_consumed')
                        ->label('إجمالي العلف المستهلك')
                        ->formatStateUsing(
                            fn ($record) => number_format(
                                $record->total_feed_consumed
                            ).' كجم',
                        )
                        ->badge()
                        ->color('info')
                        ->columnSpan(1),
                    TextEntry::make('total_feed_cost')
                        ->label('تكلفة العلف')
                        ->formatStateUsing(
                            fn ($record) => number_format(
                                $record->total_feed_cost
                            ).' EGP ',
                        )
                        ->badge()
                        ->color('warning')
                        ->columnSpan(1),
                    TextEntry::make('allocated_expenses')
                        ->label('مصروفات تشغيلية مخصصة')
                        ->formatStateUsing(
                            fn ($record) => number_format(
                                $record->allocated_expenses
                            ).' EGP ',
                        )
                        ->badge()
                        ->color('danger')
                        ->columnSpan(1),
                    TextEntry::make('total_cycle_expenses')
                        ->label('إجمالي تكاليف الدورة')
                        ->formatStateUsing(
                            fn ($record) => number_format(
                                $record->total_cycle_expenses
                            ).' EGP ',
                        )
                        ->badge()
                        ->color('danger')
                        ->columnSpan(1),
                    TextEntry::make('total_revenue')
                        ->label('إجمالي الإيرادات')
                        ->formatStateUsing(
                            fn ($record) => number_format(
                                $record->total_revenue
                            ).' EGP ',
                        )
                        ->badge()
                        ->color('success')
                        ->columnSpan(1),
                    TextEntry::make('net_profit')
                        ->label('صافي الربح')
                        ->formatStateUsing(
                            fn ($record) => number_format(
                                $record->net_profit
                            ).' EGP ',
                        )
                        ->badge()
                        ->color(
                            fn ($record) => $record->net_profit >= 0
                                ? 'success'
                                : 'danger',
                        )
                        ->columnSpan(1),
                    TextEntry::make('profit_margin')
                        ->label('هامش الربح')
                        ->formatStateUsing(
                            fn ($record) => number_format(
                                $record->profit_margin
                            ).'%',
                        )
                        ->badge()
                        ->color(
                            fn ($record) => $record->profit_margin >= 20
                                ? 'success'
                                : ($record->profit_margin >= 0
                                    ? 'warning'
                                    : 'danger'),
                        )
                        ->columnSpan(1),
                    TextEntry::make('days_since_entry')
                        ->label('عدد أيام الدورة')
                        ->formatStateUsing(
                            fn ($record) => $record->days_since_entry.' يوم',
                        )
                        ->badge()
                        ->color('info')
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(
                    fn ($record) => $record->status->value === 'harvested' ||
                        $record->is_cycle_closed,
                ),

            Section::make('إقفال الدورة')
                ->schema([
                    TextEntry::make('is_cycle_closed')
                        ->label('حالة الدورة')
                        ->formatStateUsing(
                            fn ($state) => $state ? 'مقفلة' : 'مفتوحة',
                        )
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'warning')
                        ->columnSpan(1),
                    TextEntry::make('closure_date')
                        ->label('تاريخ الإقفال')
                        ->date('Y-m-d')
                        ->placeholder('لم يتم الإقفال بعد')
                        ->columnSpan(1),
                    TextEntry::make('closedBy.name')
                        ->label('أقفل بواسطة')
                        ->placeholder('لم يتم الإقفال بعد')
                        ->columnSpan(1),
                    TextEntry::make('closure_notes')
                        ->label('ملاحظات الإقفال')
                        ->placeholder('لا توجد ملاحظات')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn ($record) => $record->is_cycle_closed),

            Section::make('ملاحظات')
                ->schema([
                    TextEntry::make('notes')
                        ->label('الملاحظات')
                        ->placeholder('لا توجد ملاحظات')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->collapsible()
                ->collapsed(fn ($record) => empty($record->notes)),
        ]);
    }
}
