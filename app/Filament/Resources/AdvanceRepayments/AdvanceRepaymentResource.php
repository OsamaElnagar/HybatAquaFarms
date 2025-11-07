<?php

namespace App\Filament\Resources\AdvanceRepayments;

use App\Filament\Resources\AdvanceRepayments\Pages\CreateAdvanceRepayment;
use App\Filament\Resources\AdvanceRepayments\Pages\EditAdvanceRepayment;
use App\Filament\Resources\AdvanceRepayments\Pages\ListAdvanceRepayments;
use App\Filament\Resources\AdvanceRepayments\Schemas\AdvanceRepaymentForm;
use App\Filament\Resources\AdvanceRepayments\Tables\AdvanceRepaymentsTable;
use App\Models\AdvanceRepayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvanceRepaymentResource extends Resource
{
    protected static ?string $model = AdvanceRepayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
