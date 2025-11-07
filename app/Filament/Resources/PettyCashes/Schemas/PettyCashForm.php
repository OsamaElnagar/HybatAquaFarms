<?php

namespace App\Filament\Resources\PettyCashes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PettyCashForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                Select::make('custodian_employee_id')
                    ->label('المستأمن')
                    ->relationship('custodian', 'name'),
                TextInput::make('opening_balance')
                    ->label('الرصيد الافتتاحي')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('opening_date')
                    ->label('تاريخ الافتتاح'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
