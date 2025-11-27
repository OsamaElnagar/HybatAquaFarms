<?php

namespace App\Filament\Resources\FeedStocks\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedStockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('warehouse.name')
                            ->label('المستودع')
                            ->columnSpan(1),
                        TextEntry::make('feedItem.name')
                            ->label('صنف العلف')
                            ->columnSpan(1),
                        TextEntry::make('warehouse.farm.name')
                            ->label('المزرعة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('feedItem.unit_of_measure')
                            ->label('وحدة القياس')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الكميات والقيم')
                    ->schema([
                        TextEntry::make('quantity_in_stock')
                            ->label('الكمية في المخزون')
                            ->formatStateUsing(fn ($record) => number_format($record->quantity_in_stock, 3).' '.($record->feedItem?->unit_of_measure ?? ''))
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('average_cost')
                            ->label('متوسط التكلفة')
                            ->formatStateUsing(fn ($state) => number_format($state).' ج.م')
                            ->columnSpan(1),
                        TextEntry::make('total_value')
                            ->label('القيمة الإجمالية')
                            ->formatStateUsing(fn ($state) => number_format($state).' ج.م')
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('last_updated')
                            ->label('آخر تحديث')
                            ->state(fn ($record) => $record->updated_at->format('Y-m-d H:i'))
                            ->badge()
                            ->color('gray')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
