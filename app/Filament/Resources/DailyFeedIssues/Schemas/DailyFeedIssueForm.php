<?php

namespace App\Filament\Resources\DailyFeedIssues\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DailyFeedIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->required(),
                Select::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'id')
                    ->required(),
                Select::make('feed_item_id')
                    ->label('صنف العلف')
                    ->relationship('feedItem', 'name')
                    ->required(),
                Select::make('feed_warehouse_id')
                    ->label('مخزن العلف')
                    ->relationship('warehouse', 'name')
                    ->required(),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required(),
                TextInput::make('quantity')
                    ->label('الكمية')
                    ->required()
                    ->numeric(),
                Select::make('batch_id')
                    ->label('دفعة الزريعة')
                    ->relationship('batch', 'id'),
                TextInput::make('recorded_by')
                    ->label('سجل بواسطة')
                    ->numeric(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
