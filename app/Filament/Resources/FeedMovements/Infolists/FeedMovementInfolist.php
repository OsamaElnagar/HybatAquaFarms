<?php

namespace App\Filament\Resources\FeedMovements\Infolists;

use App\Enums\FeedMovementType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('movement_type')
                            ->label('نوع الحركة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state instanceof FeedMovementType ? $state->getLabel() : $state)
                            ->color(fn ($state) => match ($state instanceof FeedMovementType ? $state->value : $state) {
                                'in' => 'success',
                                'out' => 'danger',
                                'transfer' => 'info',
                                default => 'gray',
                            })
                            ->columnSpan(1),
                        TextEntry::make('feedItem.name')
                            ->label('صنف العلف')
                            ->columnSpan(1),
                        TextEntry::make('date')
                            ->label('التاريخ')
                            ->date('Y-m-d')
                            ->columnSpan(1),
                        TextEntry::make('quantity')
                            ->label('الكمية')
                            ->formatStateUsing(fn ($record) => number_format($record->quantity, 3).' '.($record->feedItem?->unit_of_measure ?? ''))
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('المستودعات')
                    ->schema([
                        TextEntry::make('fromWarehouse.name')
                            ->label('من المستودع')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('toWarehouse.name')
                            ->label('إلى المستودع')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record->fromWarehouse || $record->toWarehouse),

                Section::make('معلومات إضافية')
                    ->schema([
                        TextEntry::make('factory.name')
                            ->label('المصنع')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('recordedBy.name')
                            ->label('سجل بواسطة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('description')
                            ->label('الوصف')
                            ->placeholder('لا يوجد وصف')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
