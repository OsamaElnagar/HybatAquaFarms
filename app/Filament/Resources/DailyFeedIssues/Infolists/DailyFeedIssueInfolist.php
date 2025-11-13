<?php

namespace App\Filament\Resources\DailyFeedIssues\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DailyFeedIssueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('date')
                            ->label('التاريخ')
                            ->date('Y-m-d')
                            ->columnSpan(1),
                        TextEntry::make('feedItem.name')
                            ->label('صنف العلف')
                            ->columnSpan(1),
                        TextEntry::make('quantity')
                            ->label('الكمية')
                            ->formatStateUsing(fn ($record) => number_format($record->quantity, 3).' '.($record->feedItem?->unit_of_measure ?? ''))
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('warehouse.name')
                            ->label('المستودع')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('المزرعة والوحدة')
                    ->schema([
                        TextEntry::make('farm.name')
                            ->label('المزرعة')
                            ->columnSpan(1),
                        TextEntry::make('unit.code')
                            ->label('الوحدة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('batch.batch_code')
                            ->label('الدفعة')
                            ->placeholder('غير محدد')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('معلومات إضافية')
                    ->schema([
                        TextEntry::make('recordedBy.name')
                            ->label('سجل بواسطة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
