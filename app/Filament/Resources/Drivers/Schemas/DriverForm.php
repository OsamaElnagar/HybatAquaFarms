<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DriverForm
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
                TextInput::make('phone')
                    ->label('الهاتف')
                    ->tel(),
                TextInput::make('phone2')
                    ->label('هاتف بديل')
                    ->tel(),
                TextInput::make('license_number')
                    ->label('رقم الرخصة'),
                DatePicker::make('license_expiry')
                    ->label('تاريخ انتهاء الرخصة'),
                TextInput::make('vehicle_type')
                    ->label('نوع المركبة'),
                TextInput::make('vehicle_plate')
                    ->label('رقم اللوحة'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
