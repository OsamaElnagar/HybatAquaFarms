<?php

namespace App\Filament\Resources\SalesItems;

use App\Filament\Resources\SalesItems\Pages\CreateSalesItem;
use App\Filament\Resources\SalesItems\Pages\EditSalesItem;
use App\Filament\Resources\SalesItems\Pages\ListSalesItems;
use App\Filament\Resources\SalesItems\Schemas\SalesItemForm;
use App\Filament\Resources\SalesItems\Tables\SalesItemsTable;
use App\Models\SalesItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesItemResource extends Resource
{
    protected static ?string $model = SalesItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 23;

    public static function getNavigationGroup(): ?string
    {
        return "المبيعات";
    }

    public static function getNavigationLabel(): string
    {
        return "أصناف المبيعات";
    }

    public static function getModelLabel(): string
    {
        return "صنف بيع";
    }

    public static function getPluralModelLabel(): string
    {
        return "أصناف المبيعات";
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) SalesItem::where(
            "fulfillment_status",
            "pending",
        )->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pending = SalesItem::where("fulfillment_status", "pending")->count();
        return $pending > 0 ? "warning" : "success";
    }

    public static function form(Schema $schema): Schema
    {
        return SalesItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesItemsTable::configure($table);
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
            "index" => ListSalesItems::route("/"),
            "create" => CreateSalesItem::route("/create"),
            "edit" => EditSalesItem::route("/{record}/edit"),
        ];
    }
}
