<?php

namespace App\Filament\Resources\Traders\RelationManagers;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\SalesOrders\Schemas\SalesOrderForm;
use App\Filament\Resources\SalesOrders\Tables\SalesOrdersTable;
use App\Filament\Tables\Filters\DateRangeFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalesOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'salesOrders';

    protected static ?string $title = 'فواتير المبيعات';

    protected static ?string $modelLabel = 'فاتورة بيع';

    protected static ?string $pluralModelLabel = 'فواتير بيع';

    protected static ?string $recordTitleAttribute = 'order_number';
    public function form(Schema $schema): Schema
    {
        return SalesOrderForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return SalesOrdersTable::configure($table)
            ->headerActions([
                CreateAction::make()->label('إضافة عملية مبيعات')
                    ->mutateDataUsing(function (array $data): array {
                        $data['created_by'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}