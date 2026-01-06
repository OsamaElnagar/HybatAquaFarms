<?php

namespace App\Filament\Resources\FactoryPayments;

use App\Filament\Resources\FactoryPayments\Pages\CreateFactoryPayment;
use App\Filament\Resources\FactoryPayments\Pages\EditFactoryPayment;
use App\Filament\Resources\FactoryPayments\Pages\ListFactoryPayments;
use App\Filament\Resources\FactoryPayments\Schemas\FactoryPaymentForm;
use App\Filament\Resources\FactoryPayments\Tables\FactoryPaymentsTable;
use App\Models\FactoryPayment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class FactoryPaymentResource extends Resource
{
    protected static ?string $model = FactoryPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function getNavigationGroup(): ?string
    {
        return 'الشركاء';
    }

    public static function getNavigationLabel(): string
    {
        return 'مدفوعات مصانع الأعلاف';
    }

    public static function getModelLabel(): string
    {
        return 'دفعة لمصنع';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مدفوعات مصانع الأعلاف';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['date'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return 'دفعة لمصنع - '.$record->date->format('Y-m-d').' - '.$record->factory->name;
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return FactoryPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FactoryPaymentsTable::configure($table);
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
            'index' => ListFactoryPayments::route('/'),
            'create' => CreateFactoryPayment::route('/create'),
            'edit' => EditFactoryPayment::route('/{record}/edit'),
        ];
    }
}
