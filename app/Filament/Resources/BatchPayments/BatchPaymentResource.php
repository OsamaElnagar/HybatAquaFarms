<?php

namespace App\Filament\Resources\BatchPayments;

use App\Filament\Resources\BatchPayments\Pages\CreateBatchPayment;
use App\Filament\Resources\BatchPayments\Pages\EditBatchPayment;
use App\Filament\Resources\BatchPayments\Pages\ListBatchPayments;
use App\Filament\Resources\BatchPayments\Schemas\BatchPaymentForm;
use App\Filament\Resources\BatchPayments\Tables\BatchPaymentsTable;
use App\Models\BatchPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BatchPaymentResource extends Resource
{
    protected static ?string $model = BatchPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getNavigationGroup(): ?string
    {
        return 'الزريعة';
    }

    public static function getNavigationLabel(): string
    {
        return 'مدفوعات الزريعة';
    }

    public static function getModelLabel(): string
    {
        return 'دفعة زريعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مدفوعات الزريعة';
    }

    public static function form(Schema $schema): Schema
    {
        return BatchPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BatchPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatchPayments::route('/'),
            'create' => CreateBatchPayment::route('/create'),
            'edit' => EditBatchPayment::route('/{record}/edit'),
        ];
    }
}
