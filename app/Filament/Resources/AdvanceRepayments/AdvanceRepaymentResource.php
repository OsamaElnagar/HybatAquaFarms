<?php

namespace App\Filament\Resources\AdvanceRepayments;

use App\Filament\Resources\AdvanceRepayments\Pages\CreateAdvanceRepayment;
use App\Filament\Resources\AdvanceRepayments\Pages\EditAdvanceRepayment;
use App\Filament\Resources\AdvanceRepayments\Pages\ListAdvanceRepayments;
use App\Filament\Resources\AdvanceRepayments\Schemas\AdvanceRepaymentForm;
use App\Filament\Resources\AdvanceRepayments\Tables\AdvanceRepaymentsTable;
use App\Models\AdvanceRepayment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class AdvanceRepaymentResource extends Resource
{
    protected static ?string $model = AdvanceRepayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'الموارد البشرية';
    }

    public static function getNavigationLabel(): string
    {
        return 'سداد السُلف';
    }

    public static function getModelLabel(): string
    {
        return 'سداد سلفة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'سداد السُلف';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'payment_date',
            'amount_paid',
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return 'سداد سلفة - '.$record->payment_date->format('Y-m-d').' - '.$record->employeeAdvance->employee->name;
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
        return AdvanceRepaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdvanceRepaymentsTable::configure($table);
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
            'index' => ListAdvanceRepayments::route('/'),
            'create' => CreateAdvanceRepayment::route('/create'),
            'edit' => EditAdvanceRepayment::route('/{record}/edit'),
        ];
    }
}
