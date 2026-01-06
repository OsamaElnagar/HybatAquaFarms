<?php

namespace App\Filament\Resources\FeedWarehouses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedWarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المخزن')
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('يتم إنشاؤه تلقائياً'),

                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name')
                            ->required(),

                        TextInput::make('name')
                            ->label('الاسم')
                            ->required(),
                        TextInput::make('location')
                            ->label('الموقع'),
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->required(),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
