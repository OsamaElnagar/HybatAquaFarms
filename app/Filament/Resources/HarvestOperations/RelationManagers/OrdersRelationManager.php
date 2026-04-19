<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use App\Filament\Resources\HarvestOperations\Tables\OrdersTable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'الإيصالات (أوردرات للحلقات)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('الكود')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('يتم إنشاؤه تلقائياً'),
                Select::make('harvest_id')
                    ->label('جلسة الحصاد')
                    ->relationship(
                        'harvest',
                        'harvest_number',
                        modifyQueryUsing: fn (Builder $query) => $query->where('harvest_operation_id', $this->getOwnerRecord()->id),
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->harvest_number.' - '.$record->harvest_date->format('Y-m-d'))
                    ->required(),
                Select::make('trader_id')
                    ->label('التاجر')
                    ->relationship('trader', 'name')
                    ->required(),
                Select::make('driver_id')
                    ->label('السائق')
                    ->relationship('driver', 'name'),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->displayFormat('Y-m-d')
                    ->native(false)
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }
}
