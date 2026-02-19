<?php

namespace App\Filament\Resources\PettyCashes\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PettyCashInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم العهدة')
                            ->columnSpan(1),
                        TextEntry::make('farms.name')
                            ->label('المزارع')
                            ->badge()
                            ->columnSpan(1),
                        TextEntry::make('custodian.name')
                            ->label('المستأمن')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('is_active')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'نشط' : 'غير نشط')
                            ->color(fn ($state) => $state ? 'success' : 'danger')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الأرصدة والمعاملات')
                    ->schema([
                        TextEntry::make('opening_balance')
                            ->label('الرصيد الافتتاحي')
                            ->formatStateUsing(fn ($state) => number_format($state).' EGP ')
                            ->columnSpan(1),
                        TextEntry::make('opening_date')
                            ->label('تاريخ الافتتاح')
                            ->date('Y-m-d')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('current_balance')
                            ->label('الرصيد الحالي')
                            ->formatStateUsing(fn ($state) => number_format($state).' EGP ')
                            ->badge()
                            ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                            ->columnSpan(1),
                        TextEntry::make('transactions_count')
                            ->label('عدد المعاملات')
                            ->state(fn ($record) => $record->transactions()->count())
                            ->badge()
                            ->color('primary')
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
