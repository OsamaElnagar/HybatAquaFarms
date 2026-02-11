<?php

namespace App\Filament\Resources\BatchPayments;

use App\Filament\Resources\BatchPayments\Pages\CreateBatchPayment;
use App\Filament\Resources\BatchPayments\Pages\EditBatchPayment;
use App\Filament\Resources\BatchPayments\Pages\ListBatchPayments;
use App\Filament\Resources\BatchPayments\Schemas\BatchPaymentForm;
use App\Filament\Resources\BatchPayments\Tables\BatchPaymentsTable;
use App\Models\BatchPayment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

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
                <span>{$record->batch->batch_code}</span>
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
