<?php

namespace App\Filament\Resources\BatchMovements\Schemas;

use App\Enums\MovementType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('batch_id')
                    ->label('الدفعة')
                    ->relationship('batch', 'batch_code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('اختر الدفعة المراد نقلها'),
                Select::make('movement_type')
                    ->label('نوع الحركة')
                    ->options(MovementType::class)
                    ->required()
                    ->native(false)
                    ->live()
                    ->helperText('نوع الحركة: إدخال، نقل، حصاد، نفوق'),
                Select::make('from_farm_id')
                    ->label('من المزرعة')
                    ->relationship('fromFarm', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => in_array($get('movement_type'), ['transfer', 'harvest', 'mortality']))
                    ->helperText('المزرعة المصدر (لعمليات النقل)'),
                Select::make('to_farm_id')
                    ->label('إلى المزرعة')
                    ->relationship('toFarm', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => in_array($get('movement_type'), ['entry', 'transfer']))
                    ->helperText('المزرعة الوجهة'),
                Select::make('from_unit_id')
                    ->label('من الوحدة')
                    ->relationship('fromUnit', 'code')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => in_array($get('movement_type'), ['transfer', 'harvest', 'mortality']))
                    ->helperText('الوحدة المصدر'),
                Select::make('to_unit_id')
                    ->label('إلى الوحدة')
                    ->relationship('toUnit', 'code')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => in_array($get('movement_type'), ['entry', 'transfer']))
                    ->helperText('الوحدة الوجهة'),
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
                                if (in_array($movementType, ['transfer', 'harvest', 'mortality'])) {
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
                        if (in_array($movementType, ['transfer', 'harvest', 'mortality'])) {
                            return "عدد الأسماك/الزريعة (المتاح: {$batch->current_quantity})";
                        }

                        return 'عدد الأسماك/الزريعة';
                    }),
                TextInput::make('weight')
                    ->label('الوزن (كجم)')
                    ->numeric()
                    ->step(0.001)
                    ->suffix(' كجم')
                    ->helperText('الوزن الإجمالي بالكيلوجرام'),
                DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now())
                    ->displayFormat('Y-m-d')
                    ->native(false),
                TextInput::make('reason')
                    ->label('السبب')
                    ->maxLength(255)
                    ->helperText('سبب الحركة (مثل: نقل، حصاد، نفوق)'),
                Select::make('recorded_by')
                    ->label('سجل بواسطة')
                    ->relationship('recordedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->helperText('المستخدم الذي سجل الحركة'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('أي ملاحظات إضافية حول الحركة'),
            ]);
    }
}
