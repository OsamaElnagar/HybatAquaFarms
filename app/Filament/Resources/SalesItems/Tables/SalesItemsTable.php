<?php

namespace App\Filament\Resources\SalesItems\Tables;

use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("line_number")
                    ->label("#")
                    ->sortable()
                    ->width("50px")
                    ->alignCenter(),

                TextColumn::make("salesOrder.order_number")
                    ->label("رقم الطلب")
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->url(
                        fn($record) => $record->salesOrder
                            ? SalesOrderResource::getUrl("view", [
                                "record" => $record->salesOrder,
                            ])
                            : null,
                    )
                    ->color("primary"),

                TextColumn::make("display_name")
                    ->label("الصنف")
                    ->searchable(["item_name", "description"])
                    // ->wrap()
                    ->weight("medium"),

                TextColumn::make("species.name")
                    ->label("النوع")
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color("info"),

                TextColumn::make("batch.batch_code")
                    ->label("الدفعة")
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->url(
                        fn($record) => $record->batch
                            ? BatchResource::getUrl("view", [
                                "record" => $record->batch,
                            ])
                            : null,
                    ),

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
                        Sum::make()
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
                        Sum::make()
                            ->label("المجموع")
                            ->numeric(decimalPlaces: 3)
                            ->suffix(" كجم"),
                    ]),

                TextColumn::make("average_fish_weight")
                    ->label("متوسط وزن السمكة")
                    ->numeric(decimalPlaces: 2)
                    ->suffix(" جم")
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Average::make()
                            ->label("المتوسط")
                            ->numeric(decimalPlaces: 2)
                            ->suffix(" جم"),
                    ]),

                TextColumn::make("pricing_unit")
                    ->label("وحدة التسعير")
                    ->formatStateUsing(
                        fn(string $state): string => $state === "kg"
                            ? "بالكيلو"
                            : "بالقطعة",
                    )
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make("unit_price")
                    ->label("سعر الوحدة")
                    ->numeric(decimalPlaces: 2)
                    ->prefix("ج.م ")
                    ->sortable()
                    ->toggleable(),

                TextColumn::make("discount_percent")
                    ->label("الخصم %")
                    ->numeric(decimalPlaces: 2)
                    ->suffix("%")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(fn($state) => $state > 0 ? "warning" : "gray"),

                TextColumn::make("subtotal")
                    ->label("المجموع")
                    ->numeric(decimalPlaces: 2)
                    ->prefix("ج.م ")
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make()
                            ->label("المجموع")
                            ->numeric(decimalPlaces: 2)
                            ->prefix("ج.م "),
                    ]),

                TextColumn::make("total_price")
                    ->label("الإجمالي")
                    ->numeric(decimalPlaces: 2)
                    ->prefix("ج.م ")
                    ->sortable()
                    ->weight("bold")
                    ->color("success")
                    ->summarize([
                        Sum::make()
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
                    ->sortable(),

                TextColumn::make("fulfillment_progress")
                    ->label("نسبة التنفيذ")
                    ->formatStateUsing(
                        fn($record): string => number_format(
                            $record->fulfillment_progress,
                            1,
                        ) . "%",
                    )
                    ->color(
                        fn($record): string => match (true) {
                            $record->fulfillment_progress >= 100 => "success",
                            $record->fulfillment_progress >= 50 => "warning",
                            default => "danger",
                        },
                    )
                    ->toggleable(),

                TextColumn::make("salesOrder.trader.name")
                    ->label("التاجر")
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make("salesOrder.date")
                    ->label("تاريخ الطلب")
                    ->date("Y-m-d")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make("created_at")
                    ->label("تاريخ الإضافة")
                    ->dateTime("Y-m-d H:i")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort("created_at", "desc")
            ->filters([
                SelectFilter::make("species_id")
                    ->label("نوع السمك")
                    ->relationship("species", "name")
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make("size_category")
                    ->label("فئة الحجم")
                    ->options([
                        "زريعة" => "زريعة",
                        "إصبعيات" => "إصبعيات",
                        "صغير" => "صغير",
                        "متوسط" => "متوسط",
                        "كبير" => "كبير",
                        "جامبو" => "جامبو",
                    ])
                    ->multiple(),

                SelectFilter::make("grade")
                    ->label("الدرجة")
                    ->options([
                        "A" => "درجة A",
                        "B" => "درجة B",
                        "C" => "درجة C",
                        "D" => "درجة D",
                    ])
                    ->multiple(),

                SelectFilter::make("fulfillment_status")
                    ->label("حالة التنفيذ")
                    ->options([
                        "pending" => "معلق",
                        "partial" => "جزئي",
                        "fulfilled" => "مكتمل",
                    ])
                    ->multiple(),

                SelectFilter::make("pricing_unit")
                    ->label("وحدة التسعير")
                    ->options([
                        "kg" => "بالكيلو جرام",
                        "piece" => "بالقطعة",
                    ]),

                Filter::make("has_discount")
                    ->label("به خصم")
                    ->query(
                        fn(Builder $query): Builder => $query->where(
                            "discount_percent",
                            ">",
                            0,
                        ),
                    ),

                Filter::make("high_value")
                    ->label("قيمة عالية (أكثر من 10,000 ج.م)")
                    ->query(
                        fn(Builder $query): Builder => $query->where(
                            "total_price",
                            ">",
                            10000,
                        ),
                    ),

                SelectFilter::make("batch_id")
                    ->label("الدفعة")
                    ->relationship("batch", "batch_code")
                    ->searchable()
                    ->preload(),

                SelectFilter::make("sales_order_id")
                    ->label("أمر البيع")
                    ->relationship("salesOrder", "order_number")
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
