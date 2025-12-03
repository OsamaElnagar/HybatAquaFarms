<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountForm
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
                Select::make('type')
                    ->label('النوع')
                    ->options(AccountType::class)
                    ->required(),
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name'),
                Select::make('parent_id')
                    ->label('الحساب الأب')
                    ->relationship('parent', 'name'),
                Toggle::make('is_active')
                    ->label('نشط')
                    ->required(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
            ]);
    }
}
