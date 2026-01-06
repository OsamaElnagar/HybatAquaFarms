<?php

namespace App\Filament\Resources\HarvestOperations\RelationManagers;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'بكس وعناصر كل أوردر';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('order_id')
                    ->label('الطلب')
                    ->options(function () {
                        return Order::where('harvest_operation_id', $this->getOwnerRecord()->id)
                            ->get()
                            ->mapWithKeys(fn (Order $order) => [
                                $order->id => $order->code.' - '.$order->date->format('Y-m-d'),
                            ]);
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('box_id')
                    ->label('صنف البوكسه')
                    ->relationship('box', 'name', modifyQueryUsing: function (Builder $query) {
                        return $query->select('*'); // Ensure we can access accessors if needed, though name is a column
                    })
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('عدد البكس')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\TextInput::make('weight_per_box')
                    ->label('وزن البوكسه')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('order.code')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('box.full_name')
                    ->label('صنف البوكسه')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('عدد البكس')
                    ->summarize(Sum::make()->label('إجمالي العدد')->numeric(locale: 'en'))
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('weight_per_box')
                    ->label('وزن البوكسه')
                    ->numeric(decimalPlaces: 0, locale: 'en')
                    ->sortable(),
                TextColumn::make('total_weight')
                    ->label('إجمالي الوزن')
                    ->summarize(Sum::make()->label('إجمالي الوزن')->numeric(locale: 'en'))
                    ->numeric(decimalPlaces: 0, locale: 'en')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة صنف وعدد بكس')
                    ->using(function (array $data, string $model): Model {
                        return $model::create($data);
                    }),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل '),
                DeleteAction::make()->label('حذف '),
                ReplicateAction::make()->label('استنساخ '),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف العناصر'),
                ]),
            ]);
    }
}
