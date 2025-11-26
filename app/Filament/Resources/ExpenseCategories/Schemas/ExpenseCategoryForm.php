<?php

namespace App\Filament\Resources\ExpenseCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExpenseCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('الكود')
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('كود فريد للفئة (اختياري)'),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull()
                    ->maxLength(1000),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true)
                    ->required(),
            ]);
    }
}
