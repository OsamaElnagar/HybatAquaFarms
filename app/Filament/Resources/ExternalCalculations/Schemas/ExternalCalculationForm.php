<?php

namespace App\Filament\Resources\ExternalCalculations\Schemas;

use App\Enums\AccountType;
use App\Enums\ExternalCalculationType;
use App\Models\Account;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ExternalCalculationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ,
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required(),
                Select::make('type')
                    ->label('النوع')
                    ->options(ExternalCalculationType::class)
                    ->required()
                    ->live(),
                Select::make('treasury_account_id')
                    ->label('الخزنة / البنك')
                    ->relationship('treasuryAccount', 'name', fn ($query, Get $get) => $query
                        ->where('is_treasury', true)
                        // ->when($get('farm_id'), fn ($q) => $q->where('farm_id', $get('farm_id')))
                        )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('account_id')
                    ->label('الحساب المقابل')
                    ->relationship('account', 'name', function ($query, Get $get) {
                        $type = $get('type');
                        if ($type === ExternalCalculationType::Payment) {
                            $query->where('type', AccountType::Expense);
                        } elseif ($type === ExternalCalculationType::Receipt) {
                            $query->where('type', AccountType::Income);
                        } else {
                            $query->whereNull('id');
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric(),
                TextInput::make('reference_number')
                    ->label('رقم المرجع'),
                Textarea::make('description')
                    ->label('الوصف')
                    ->columnSpanFull(),
                Hidden::make('created_by')
                    ->default(auth('web')->id()),
            ]);
    }
}
