<?php

namespace App\Filament\Resources\SalesOrders\RelationManagers;

use App\Filament\Resources\SalesItems\Schemas\SalesItemForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = "items";

    protected static ?string $title = "أصناف العملية";

    public function form(Schema $schema): Schema
    {
        return SalesItemForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute("item_name")
            ->columns([
                TextColumn::make("line_number")
                    ->label("#")
                    ->sortable()
                    ->width("50px")
                    ->alignCenter(),

                TextColumn::make("display_name")
                    ->label("الصنف")
                    ->searchable(["item_name", "description"])

                    ->weight("medium"),

                TextColumn::make("species.name")
                    ->label("النوع")
                    ->sortable()
                    ->badge()
                    ->color("info")
                    ->toggleable(),

                TextColumn::make("batch.batch_code")
                    ->label("الدفعة")
                    ->sortable()
                    ->toggleable(),

                TextColumn::make("size_category")
                    ->label("الحجم")
                    ->badge()
                    ->color(
                        fn(string $state = null): string => match ($state) {
                            "جامبو" => "success",
                            "كبير" => "primary",
                            "متوسط" => "info",
                            "صغير" => "warning",
                            default => "gray",
                        },
                    )
                    ->toggleable(),

                TextColumn::make("grade")
                    ->label("الدرجة")
                    ->badge()
                    ->color(
                        fn(string $state = null): string => match ($state) {
                            "A" => "success",
                            "B" => "primary",
                            "C" => "warning",
                            "D" => "danger",
                            default => "gray",
                        },
                    )
                    ->toggleable(),

                TextColumn::make("quantity")
                    ->label("العدد")
                    ->numeric(decimalPlaces: 0)
                    ->suffix(" قطعة")
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label("المجموع")
                            ->numeric(decimalPlaces: 0)
                            ->suffix(" قطعة"),
                    ]),

                TextColumn::make("weight_kg")
                    ->label("الوزن")
                    ->numeric(decimalPlaces: 3)
                    ->suffix(" كجم")
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label("المجموع")
                            ->numeric(decimalPlaces: 3)
                            ->suffix(" كجم"),
                    ]),

                TextColumn::make("average_fish_weight")
                    ->label("متوسط وزن السمكة")
                    ->numeric(decimalPlaces: 2)
                    ->suffix(" جم")
                    ->toggleable()
                    ->toggledHiddenByDefault(),

                TextColumn::make("unit_price")
                    ->label("سعر الوحدة")
                    ->numeric(decimalPlaces: 2)
                    ->prefix("ج.م ")
                    ->sortable(),

                TextColumn::make("discount_percent")
                    ->label("الخصم %")
                    ->numeric(decimalPlaces: 2)
                    ->suffix("%")
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->color(fn($state) => $state > 0 ? "warning" : "gray"),

                TextColumn::make("total_price")
                    ->label("الإجمالي")
                    ->numeric(decimalPlaces: 2)
                    ->prefix("ج.م ")
                    ->sortable()
                    ->weight("bold")
                    ->color("success")
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->label("الإجمالي الكلي")
                            ->numeric(decimalPlaces: 2)
                            ->prefix("ج.م "),
                    ]),

                TextColumn::make("fulfillment_status")
                    ->label("حالة التنفيذ")
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            "fulfilled" => "success",
                            "partial" => "warning",
                            "pending" => "danger",
                            default => "gray",
                        },
                    )
                    ->formatStateUsing(
                        fn(string $state): string => match ($state) {
                            "fulfilled" => "مكتمل",
                            "partial" => "جزئي",
                            "pending" => "معلق",
                            default => $state,
                        },
                    )
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label("إضافة صنف")
                    ->mutateDataUsing(function (array $data): array {
                        $data["sales_order_id"] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort("line_number", "asc");
    }
}
