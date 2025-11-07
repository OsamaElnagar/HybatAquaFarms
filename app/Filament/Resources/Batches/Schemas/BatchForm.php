<?php

namespace App\Filament\Resources\Batches\Schemas;

use App\Enums\BatchStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('batch_code')
                    ->label('كود الدفعة')
                    ->required(),
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->required(),
                Select::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'id'),
                Select::make('species_id')
                    ->label('النوع')
                    ->relationship('species', 'name')
                    ->required(),
                DatePicker::make('entry_date')
                    ->label('تاريخ الإدخال')
                    ->required(),
                TextInput::make('initial_quantity')
                    ->label('الكمية الأولية')
                    ->required()
                    ->numeric(),
                TextInput::make('current_quantity')
                    ->label('الكمية الحالية')
                    ->required()
                    ->numeric(),
                TextInput::make('initial_weight_avg')
                    ->label('متوسط الوزن الأولي')
                    ->numeric(),
                TextInput::make('current_weight_avg')
                    ->label('متوسط الوزن الحالي')
                    ->numeric(),
                TextInput::make('source')
                    ->label('المصدر'),
                Select::make('status')
                    ->label('الحالة')
                    ->options(BatchStatus::class)
                    ->default('active')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
