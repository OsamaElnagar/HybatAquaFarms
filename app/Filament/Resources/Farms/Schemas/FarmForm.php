<?php

namespace App\Filament\Resources\Farms\Schemas;

use App\Enums\FarmStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FarmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('الكود')
                    ->required(),
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                TextInput::make('size')
                    ->label('المساحة')
                    ->numeric(),
                TextInput::make('location')
                    ->label('الموقع'),
                TextInput::make('latitude')
                    ->label('خط العرض')
                    ->numeric(),
                TextInput::make('longitude')
                    ->label('خط الطول')
                    ->numeric(),
                TextInput::make('capacity')
                    ->label('السعة')
                    ->numeric(),
                Select::make('status')
                    ->label('الحالة')
                    ->options(FarmStatus::class)
                    ->default('active')
                    ->required(),
                DatePicker::make('established_date')
                    ->label('تاريخ التأسيس'),
                Select::make('manager_id')
                    ->label('المدير')
                    ->relationship('manager', 'name'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
