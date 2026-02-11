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
        return 'مدفوعات المصانع والموردين';
    }

    public static function getModelLabel(): string
    {
        return 'دفعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مدفوعات المصانع والموردين';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['date'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        $color = match ($record->payment_method?->getColor()) {
            'success' => 'green',
            'info' => 'blue',
            'warning' => 'yellow',
            'danger' => 'red',
            default => 'gray',
        };

        return new \Illuminate\Support\HtmlString(
            "<div class='flex items-center gap-2'>
                <span>{$record->factory->name}</span>
                <span class='text-gray-500 text-sm'>({$record->date->format('Y-m-d')})</span>
                <span class='px-2 py-0.5 rounded text-xs font-medium bg-{$color}-100 text-{$color}-700'>
                    {$record->payment_method?->getLabel()}
                </span>
                <span class='font-bold text-sm'>".number_format($record->amount).' EGP</span>
            </div>'
        );
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
