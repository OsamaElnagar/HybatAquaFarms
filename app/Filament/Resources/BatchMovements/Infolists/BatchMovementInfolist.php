<?php

namespace App\Filament\Resources\BatchMovements\Infolists;

use App\Enums\MovementType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BatchMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('batch.batch_code')
                            ->label('كود الدفعة')
                            ->columnSpan(1),
                        TextEntry::make('movement_type')
                            ->label('نوع الحركة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state instanceof MovementType ? $state->getLabel() : $state)
                            ->color(fn ($state) => match ($state instanceof MovementType ? $state->value : $state) {
                                'entry' => 'success',
                                'transfer' => 'info',
                                'harvest' => 'warning',
                                'mortality' => 'danger',
                                default => 'gray',
                            })
                            ->columnSpan(1),
                        TextEntry::make('date')
                            ->label('التاريخ')
                            ->date('Y-m-d')
                            ->columnSpan(1),
                        TextEntry::make('quantity')
                            ->label('الكمية')
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الموقع')
                    ->schema([
                        TextEntry::make('fromFarm.name')
                            ->label('من المزرعة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('toFarm.name')
                            ->label('إلى المزرعة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('fromUnit.code')
                            ->label('من الوحدة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('toUnit.code')
                            ->label('إلى الوحدة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record->fromFarm || $record->toFarm || $record->fromUnit || $record->toUnit),

                Section::make('معلومات إضافية')
                    ->schema([
                        TextEntry::make('weight')
                            ->label('الوزن')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 3).' كجم' : 'غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('reason')
                            ->label('السبب')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
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
