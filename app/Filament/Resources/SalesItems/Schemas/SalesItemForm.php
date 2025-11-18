<?php

namespace App\Filament\Resources\SalesItems\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SalesItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make("معلومات الطلب")
                ->description("تفاصيل أمر البيع والدفعة")
                ->icon("heroicon-o-document-text")
                ->schema([
                    Grid::make(3)->schema([
                        Select::make("sales_order_id")
                            ->label("أمر البيع")
                            ->relationship("salesOrder", "order_number")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->native(false),

                        Select::make("batch_id")
                            ->label("الدفعة")
                            ->relationship(
                                "batch",
                                "batch_code",
                                fn($query) => $query->where(
                                    "status",
                                    "!=",
                                    "completed",
                                ),
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $batch = \App\Models\Batch::find($state);
                                    if ($batch) {
                                        $set("species_id", $batch->species_id);
                                    }
                                }
                            })
                            ->columnSpan(1)
                            ->native(false),

                        Select::make("species_id")
                            ->label("نوع السمك")
                            ->relationship("species", "name")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->native(false),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make("item_name")
                            ->label("اسم الصنف")
                            ->placeholder("مثال: بلطي جامبو درجة أولى")
                            ->columnSpan(1),

                        TextInput::make("line_number")
                            ->label("رقم السطر")
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->columnSpan(1),
                    ]),
                ])
                ->collapsible()
                ->columns(2),

            Section::make("التصنيف والجودة")
                ->description("معايير الحجم والجودة")
                ->icon("heroicon-o-star")
                ->schema([
                    Grid::make(2)->schema([
                        Select::make("size_category")
                            ->label("فئة الحجم")
                            ->options([
                                "زريعة" => "زريعة (0-10 جم)",
                                "إصبعيات" => "إصبعيات (10-50 جم)",
                                "صغير" => "صغير (50-150 جم)",
                                "متوسط" => "متوسط (150-300 جم)",
                                "كبير" => "كبير (300-500 جم)",
                                "جامبو" => "جامبو (500+ جم)",
                            ])
                            ->native(false)
                            ->columnSpan(1),

                        Select::make("grade")
                            ->label("الدرجة/الجودة")
                            ->options([
                                "A" => "درجة A (ممتاز)",
                                "B" => "درجة B (جيد جداً)",
                                "C" => "درجة C (جيد)",
                                "D" => "درجة D (مقبول)",
                            ])
                            ->native(false)
                            ->columnSpan(1),
                    ]),
                ])
                ->collapsible()
                ->columns(2),

            Section::make("الكميات والأوزان")
                ->description("الكمية والوزن الإجمالي")
                ->icon("heroicon-o-scale")
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make("quantity")
                            ->label("العدد (قطعة/سمكة)")
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(1)
                            ->suffix("قطعة")
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculateAverageWeight($get, $set);
                            })
                            ->columnSpan(1),

                        TextInput::make("weight_kg")
                            ->label("الوزن الإجمالي")
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.001)
                            ->suffix("كجم")
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculateAverageWeight($get, $set);
                                self::calculatePricing($get, $set);
                            })
                            ->columnSpan(1),

                        Placeholder::make("average_fish_weight_display")
                            ->label("متوسط وزن السمكة")
                            ->content(function (Get $get): string {
                                $quantity = floatval($get("quantity") ?? 0);
                                $weight = floatval($get("weight_kg") ?? 0);

                                if ($quantity > 0 && $weight > 0) {
                                    $avg = ($weight * 1000) / $quantity;
                                    return number_format($avg, 2) . " جرام";
                                }

                                return "---";
                            })
                            ->columnSpan(1),
                    ]),
                ])
                ->collapsible()
                ->columns(3),

            Section::make("التسعير")
                ->description("الأسعار والخصومات")
                ->icon("heroicon-o-currency-dollar")
                ->schema([
                    Grid::make(4)->schema([
                        Select::make("pricing_unit")
                            ->label("وحدة التسعير")
                            ->options([
                                "kg" => "بالكيلو جرام",
                                "piece" => "بالقطعة",
                            ])
                            ->default("kg")
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculatePricing($get, $set);
                            })
                            ->columnSpan(1),

                        TextInput::make("unit_price")
                            ->label("سعر الوحدة")
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix("ج.م")
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculatePricing($get, $set);
                            })
                            ->columnSpan(1),

                        TextInput::make("discount_percent")
                            ->label("نسبة الخصم %")
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix("%")
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculatePricing($get, $set);
                            })
                            ->columnSpan(1),

                        Placeholder::make("discount_amount_display")
                            ->label("قيمة الخصم")
                            ->content(function (Get $get): string {
                                $discount = floatval(
                                    $get("discount_amount") ?? 0,
                                );
                                return "ج.م " . number_format($discount, 2);
                            })
                            ->columnSpan(1),
                    ]),

                    Grid::make(3)->schema([
                        Placeholder::make("subtotal_display")
                            ->label("المجموع قبل الخصم")
                            ->content(function (Get $get): string {
                                $subtotal = floatval($get("subtotal") ?? 0);
                                return "ج.م " . number_format($subtotal, 2);
                            })
                            ->columnSpan(1),

                        Placeholder::make("total_price_display")
                            ->label("الإجمالي النهائي")
                            ->content(function (Get $get): string {
                                $total = floatval($get("total_price") ?? 0);
                                return "ج.م " . number_format($total, 2);
                            })
                            ->columnSpan(2)
                            ->extraAttributes([
                                "class" =>
                                    "text-2xl font-bold text-success-600",
                            ]),
                    ]),

                    // Hidden fields for calculated values
                    TextInput::make("subtotal")->hidden()->dehydrated(),

                    TextInput::make("discount_amount")->hidden()->dehydrated(),

                    TextInput::make("total_price")->hidden()->dehydrated(),

                    TextInput::make("average_fish_weight")
                        ->hidden()
                        ->dehydrated(),
                ])
                ->collapsible()
                ->columns(4),

            Section::make("حالة التنفيذ")
                ->description("تتبع تنفيذ الطلب")
                ->icon("heroicon-o-truck")
                ->schema([
                    Grid::make(3)->schema([
                        Select::make("fulfillment_status")
                            ->label("حالة التنفيذ")
                            ->options([
                                "pending" => "معلق",
                                "partial" => "جزئي",
                                "fulfilled" => "مكتمل",
                            ])
                            ->default("pending")
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        TextInput::make("fulfilled_quantity")
                            ->label("الكمية المنفذة")
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix("قطعة")
                            ->columnSpan(1),

                        TextInput::make("fulfilled_weight")
                            ->label("الوزن المنفذ")
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix("كجم")
                            ->columnSpan(1),
                    ]),
                ])
                ->collapsible()
                ->collapsed()
                ->columns(3),

            Section::make("معلومات إضافية")
                ->description("ملاحظات ووصف")
                ->icon("heroicon-o-information-circle")
                ->schema([
                    Textarea::make("description")
                        ->label("الوصف")
                        ->rows(2)
                        ->columnSpanFull(),

                    Textarea::make("notes")
                        ->label("ملاحظات")
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed()
                ->columns(1),
        ]);
    }

    protected static function calculateAverageWeight(Get $get, Set $set): void
    {
        $quantity = floatval($get("quantity") ?? 0);
        $weight = floatval($get("weight_kg") ?? 0);

        if ($quantity > 0 && $weight > 0) {
            $average = ($weight * 1000) / $quantity;
            $set("average_fish_weight", round($average, 3));
        }
    }

    protected static function calculatePricing(Get $get, Set $set): void
    {
        $pricingUnit = $get("pricing_unit") ?? "kg";
        $unitPrice = floatval($get("unit_price") ?? 0);
        $quantity = floatval($get("quantity") ?? 0);
        $weight = floatval($get("weight_kg") ?? 0);
        $discountPercent = floatval($get("discount_percent") ?? 0);

        // Calculate subtotal based on pricing unit
        if ($pricingUnit === "piece" && $quantity > 0) {
            $subtotal = $quantity * $unitPrice;
        } else {
            $subtotal = $weight * $unitPrice;
        }

        // Calculate discount
        $discountAmount = $subtotal * ($discountPercent / 100);

        // Calculate total
        $total = $subtotal - $discountAmount;

        $set("subtotal", round($subtotal, 2));
        $set("discount_amount", round($discountAmount, 2));
        $set("total_price", round($total, 2));
    }
}
