<?php

namespace App\Filament\Resources\FeedItems\Schemas;

use App\Enums\FactoryType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class FeedItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('الكود')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('يتم توليده تلقائياً'),
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                Select::make('factory_id')
                    ->label('المصنع')
                    ->relationship('factory', 'name', function (Builder $query) {
                        return $query->where('type', FactoryType::FEEDS);
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
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
                    ->default(true)
                    ->required(),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
