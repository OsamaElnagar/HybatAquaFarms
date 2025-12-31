<?php

namespace App\Filament\Resources\Boxes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BoxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات البوكسه')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم البوكس')
                            ->required(),
                        Select::make('species_id')
                            ->label('النوع')
                            ->relationship('species', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        TextInput::make('max_weight')
                            ->label('الوزن الأقصى')
                            ->helperText('الوزن الأقصى للبوكس لكى نتفادى أخطاء كتابة أوزان غير منطقية'),
                        TextInput::make('class')
                            ->label('التصنيف'),
                        TextInput::make('category')
                            ->label('الفئة'),
                        TextInput::make('class_total_weight')
                            ->label('وزن البوكسه من هذه الفئة (شامل الاسماك)')
                            ->numeric(),
                    ]),
            ]);
    }
}
