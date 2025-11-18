<?php

namespace App\Filament\Resources\Farms\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class FarmInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('code')
                            ->label('الكود')
                            ->columnSpan(1),
                        TextEntry::make('name')
                            ->label('الاسم')
                            ->columnSpan(1),
                        TextEntry::make('size')
                            ->label('المساحة')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('location')
                            ->label('الموقع')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الحالة والإدارة')
                    ->schema([
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->columnSpan(1),
                        TextEntry::make('manager.name')
                            ->label('المدير')
                            ->placeholder('لا يوجد مدير')
                            ->columnSpan(1),
                        TextEntry::make('established_date')
                            ->label('تاريخ التأسيس')
                            ->date('Y-m-d')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('إحصائيات المزرعة')
                    ->schema([
                        TextEntry::make('units_count')
                            ->label('عدد الوحدات')
                            ->state(fn ($record) => $record->units()->count())
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('active_batches')
                            ->label('الدفعات النشطة')
                            ->state(fn ($record) => $record->active_batches_count)
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('total_current_stock')
                            ->label('المخزون الحالي')
                            ->state(fn ($record) => number_format($record->total_current_stock))
                            ->badge()
                            ->color('info')
                            ->columnSpan(1),
                        TextEntry::make('total_batches')
                            ->label('إجمالي الدفعات')
                            ->state(fn ($record) => $record->batches()->count())
                            ->badge()
                            ->color('warning')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('استهلاك الأعلاف')
                    ->schema([
                        TextEntry::make('feed_today')
                            ->label('اليوم')
                            ->state(fn ($record) => number_format($record->getTotalFeedConsumed(
                                Carbon::today()->format('Y-m-d'),
                                Carbon::today()->format('Y-m-d')
                            ), 2).' كجم')
                            ->badge()
                            ->color('info')
                            ->columnSpan(1),
                        TextEntry::make('feed_this_week')
                            ->label('هذا الأسبوع')
                            ->state(fn ($record) => number_format($record->getTotalFeedConsumed(
                                Carbon::now()->startOfWeek()->format('Y-m-d'),
                                Carbon::today()->format('Y-m-d')
                            ), 2).' كجم')
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('feed_this_month')
                            ->label('هذا الشهر')
                            ->state(fn ($record) => number_format($record->getTotalFeedConsumed(
                                Carbon::now()->startOfMonth()->format('Y-m-d'),
                                Carbon::today()->format('Y-m-d')
                            ), 2).' كجم')
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('feed_total')
                            ->label('الإجمالي')
                            ->state(fn ($record) => number_format($record->getTotalFeedConsumed(), 2).' كجم')
                            ->badge()
                            ->color('warning')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

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
