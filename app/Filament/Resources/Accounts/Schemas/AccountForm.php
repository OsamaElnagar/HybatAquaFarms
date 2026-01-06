<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('البيانات الأساسية')
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('يتم توليده تلقائياً'),
                        TextInput::make('name')
                            ->label('اسم الحساب')
                            ->required(),
                        Select::make('type')
                            ->label('نوع الحساب')
                            ->options(AccountType::class)
                            ->required(),
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name'),
                        Select::make('parent_id')
                            ->label('الحساب الأب')
                            ->relationship('parent', 'name'),
                    ]),
                Fieldset::make('إعدادات الحالة والخزنة')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->required(),
                        Toggle::make('is_treasury')
                            ->label('حساب خزينة')
                            ->helperText('حدد هذا الخيار إذا كان الحساب يمثل صندوق نقدية أو حساب بنكي ليظهر في لوحة تحكم الخزنة')
                            ->default(false),
                    ]),
                Fieldset::make('الوصف')
                    ->schema([
                        Textarea::make('description')
                            ->label('الوصف')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
