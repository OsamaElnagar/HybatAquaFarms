<?php

namespace App\Filament\Resources\FarmUnits\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FarmUnitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('farm.name')
                            ->label('المزرعة')
                            ->columnSpan(1),
                        TextEntry::make('name')
                            ->label('الاسم')
                            ->copyable()
                            ->copyMessage('تم نسخ الاسم')
                            ->columnSpan(1),
                            TextEntry::make('code')
                            ->label('الكود')
                            ->copyable()
                            ->copyMessage('تم نسخ الكود')
                            ->columnSpan(1),
                        TextEntry::make('unit_type')
                            ->label('نوع الوحدة')
                            ->badge()
                            ->columnSpan(1),
                        TextEntry::make('capacity')
                            ->label('السعة')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state) : 'غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الحالة والإحصائيات')
                    ->schema([
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->columnSpan(1),
                        TextEntry::make('batches_count')
                            ->label('عدد الدفعات')
                            ->state(fn ($record) => $record->batches()->count())
                            ->badge()
                            ->color(fn ($state) => $state > 0 ? 'success' : 'warning')
                            ->columnSpan(1),
                        TextEntry::make('active_batches')
                            ->label('الدفعات النشطة')
                            ->state(fn ($record) => $record->batches()->where('status', 'active')->count())
                            ->badge()
                            ->color('info')
                            ->columnSpan(1),
                        TextEntry::make('current_stock')
                            ->label('المخزون الحالي')
                            ->state(fn ($record) => number_format($record->total_current_stock))
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('daily_feed_issues_count')
                            ->label('سجلات صرف الأعلاف')
                            ->state(fn ($record) => $record->dailyFeedIssues()->count())
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i')
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
