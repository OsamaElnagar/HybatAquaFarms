<?php

namespace App\Filament\Resources\FeedItems\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeedItemForm
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
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                TextInput::make('unit_of_measure')
                    ->label('وحدة القياس')
                    ->required()
                    ->default('kg'),
                TextInput::make('standard_cost')
                    ->label('التكلفة القياسية')
                    ->numeric(),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
