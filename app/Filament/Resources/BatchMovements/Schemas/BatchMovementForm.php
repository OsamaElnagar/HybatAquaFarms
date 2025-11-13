<?php

namespace App\Filament\Resources\BatchMovements\Schemas;

use App\Enums\MovementType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BatchMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل الحركة')
                    ->schema([
                        Select::make('batch_id')
                            ->label('الدفعة')
                            ->relationship('batch', 'batch_code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('اختر الدفعة المراد نقلها')
                            ->columnSpan(1),
                        Select::make('movement_type')
                            ->label('نوع الحركة')
                            ->options(MovementType::class)
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText('نوع الحركة: إدخال، نقل، حصاد، نفوق')
                            ->columnSpan(1),
                        Select::make('from_farm_id')
                            ->label('من المزرعة')
                            ->relationship('fromFarm', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('from_unit_id', null))
                            ->visible(fn (Get $get): bool => self::isFromFarmVisible($get))
                            ->required(fn (Get $get): bool => self::isFromFarmVisible($get))
                            ->helperText('المزرعة المصدر (لعمليات النقل، الحصاد، والنفوق)')
                            ->columnSpan(1),
                        Select::make('to_farm_id')
                            ->label('إلى المزرعة')
                            ->relationship('toFarm', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('to_unit_id', null))
                            ->visible(fn (Get $get): bool => self::isToFarmVisible($get))
                            ->required(fn (Get $get): bool => self::isToFarmVisible($get))
                            ->helperText('المزرعة الوجهة (لعمليات الإدخال والنقل)')
                            ->columnSpan(1),
                        Select::make('from_unit_id')
                            ->label('من الوحدة')
                            ->options(fn (Get $get): array => $get('from_farm_id')
                                    ? \App\Models\FarmUnit::query()
                                        ->where('farm_id', $get('from_farm_id'))
                                        ->orderBy('code')
                                        ->pluck('code', 'id')
                                        ->all()
                                    : []
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get): bool => self::isFromUnitVisible($get))
                            ->required(fn (Get $get): bool => self::isFromUnitVisible($get))
                            ->helperText('الوحدة المصدر')
                            ->columnSpan(1),
                        Select::make('to_unit_id')
                            ->label('إلى الوحدة')
                            ->options(fn (Get $get): array => $get('to_farm_id')
                                    ? \App\Models\FarmUnit::query()
                                        ->where('farm_id', $get('to_farm_id'))
                                        ->orderBy('code')
                                        ->pluck('code', 'id')
                                        ->all()
                                    : []
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get): bool => self::isToUnitVisible($get))
                            ->required(fn (Get $get): bool => self::isToUnitVisible($get))
                            ->helperText('الوحدة الوجهة')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('بيانات إضافية')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->live()
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $batchId = $get('batch_id');
                                        $movementType = $get('movement_type');

                                        if (! $batchId || ! $movementType) {
                                            return;
                                        }

                                        $batch = \App\Models\Batch::find($batchId);
                                        if (! $batch) {
                                            return;
                                        }

                                        // For movements that decrease quantity (transfer, harvest, mortality)
                                        if (
                                            $movementType === MovementType::Transfer ||
                                            $movementType === MovementType::Harvest ||
                                            $movementType === MovementType::Mortality
                                        ) {
                                            if ($value > $batch->current_quantity) {
                                                $fail("الكمية ({$value}) تتجاوز الكمية المتاحة في الدفعة ({$batch->current_quantity}).");
                                            }
                                        }
                                    };
                                },
                            ])
                            ->helperText(function ($get) {
                                $batchId = $get('batch_id');
                                if (! $batchId) {
                                    return 'عدد الأسماك/الزريعة';
                                }

                                $batch = \App\Models\Batch::find($batchId);
                                if (! $batch) {
                                    return 'عدد الأسماك/الزريعة';
                                }

                                $movementType = $get('movement_type');
                                if (
                                    $movementType === MovementType::Transfer ||
                                    $movementType === MovementType::Harvest ||
                                    $movementType === MovementType::Mortality
                                ) {
                                    return "عدد الأسماك/الزريعة (المتاح: {$batch->current_quantity})";
                                }

                                return 'عدد الأسماك/الزريعة';
                            })
                            ->columnSpan(1),
                        TextInput::make('weight')
                            ->label('الوزن (كجم)')
                            ->numeric()
                            ->step(0.001)
                            ->suffix(' كجم')
                            ->helperText('الوزن الإجمالي بالكيلوجرام')
                            ->columnSpan(1),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->columnSpan(1),
                        TextInput::make('reason')
                            ->label('السبب')
                            ->maxLength(255)
                            ->helperText('سبب الحركة (مثل: نقل، حصاد، نفوق)')
                            ->columnSpan(1),
                        Select::make('recorded_by')
                            ->label('سجل بواسطة')
                            ->relationship('recordedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth('web')->id())
                            ->helperText('المستخدم الذي سجل الحركة')
                            ->columnSpan(2),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('أي ملاحظات إضافية حول الحركة'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }

    protected static function isFromFarmVisible(Get $get): bool
    {
        $movementType = $get('movement_type');

        return in_array($movementType, [
            MovementType::Transfer,
            MovementType::Harvest,
            MovementType::Mortality,
        ]);
    }

    protected static function isToFarmVisible(Get $get): bool
    {
        $movementType = $get('movement_type');

        return in_array($movementType, [
            MovementType::Entry,
            MovementType::Transfer,
        ]);
    }

    protected static function isFromUnitVisible(Get $get): bool
    {
        $movementType = $get('movement_type');

        return in_array($movementType, [
            MovementType::Transfer,
            MovementType::Harvest,
            MovementType::Mortality,
        ]) && $get('from_farm_id');
    }

    protected static function isToUnitVisible(Get $get): bool
    {
        $movementType = $get('movement_type');

        return in_array($movementType, [
            MovementType::Entry,
            MovementType::Transfer,
        ]) && $get('to_farm_id');
    }
}
