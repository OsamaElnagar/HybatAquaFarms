<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Filament\Resources\FeedStocks\Schemas\FeedStockForm;
use App\Filament\Resources\FeedStocks\Tables\FeedStocksTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FeedStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'feedStocks';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'أرصدة الأعلاف';
    }

    public function form(Schema $schema): Schema
    {
        return FeedStockForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return FeedStocksTable::configure($table)
            ->headerActions([
                CreateAction::make()->label('إضافة رصيد جديد')->icon('heroicon-o-plus')
            ]);
    }
}
