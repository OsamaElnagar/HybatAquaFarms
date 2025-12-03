<?php

namespace App\Filament\Resources\Factories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FactoryForm
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
                TextInput::make('contact_person')
                    ->label('الشخص المسؤول'),
                TextInput::make('phone')
                    ->label('الهاتف')
                    ->tel(),
                TextInput::make('phone2')
                    ->label('هاتف بديل')
                    ->tel(),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email(),
                Textarea::make('address')
                    ->label('العنوان')
                    ->columnSpanFull(),
                TextInput::make('payment_terms_days')
                    ->label('أيام الدفع')
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
