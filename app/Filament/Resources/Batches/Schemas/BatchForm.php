<?php

namespace App\Filament\Resources\Batches\Schemas;

use App\Enums\BatchStatus;
use App\Enums\FactoryType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make('معلومات أساسية')
                    ->description('المعلومات الأساسية للدفعة')
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('batch_code')
                                    ->label('كود الدفعة')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('كود فريد للدفعة (مثل: BATCH-2024-001)')
                                    ->columnSpan(1),

                                DatePicker::make('entry_date')
                                    ->label('تاريخ الإدخال')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('Y-m-d')
                                    ->native(false)
                                    ->helperText('تاريخ إدخال الزريعة إلى المزرعة')
                                    ->columnSpan(1),
                            ]),

                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('farm_id')
                                    ->label('المزرعة')
                                    ->default(fn ($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
                                    ->disabled(fn ($livewire) => $livewire instanceof RelationManager)
                                    ->relationship('farm', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('units', []))
                                    ->helperText('اختر المزرعة التي سيتم إدخال الزريعة فيها')
                                    ->columnSpan(1),

                                Select::make('units')
                                    ->label('الوحدات')
                                    ->relationship('units', 'name', function (Builder $query, callable $get) {
                                        $farmId = $get('farm_id');
                                        if (! $farmId) {
                                            return $query->whereRaw('1=0');
                                        }

                                        return $query->where('farm_id', $farmId);
                                    })
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->helperText('اختر الوحدات (الأحواض/الخزانات) - سيتم عرض وحدات المزرعة المختارة فقط')
                                    ->columnSpan(1),
                            ]),

                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('species_id')
                                    ->label('نوع الأسماك/الزريعة')
                                    ->relationship('species', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('نوع الأسماك أو الزريعة (مثل: بلطي، بوري، قاروص)')
                                    ->columnSpan(1),

                                Select::make('status')
                                    ->label('الحالة')
                                    ->options(BatchStatus::class)
                                    ->default('active')
                                    ->required()
                                    ->native(false)
                                    ->helperText('حالة الدفعة: نشط، محصود، مستنفذ')
                                    ->columnSpan(1),
                            ]),
                    ]),

                SchemaSection::make('معلومات المورد والتكلفة')
                    ->description('معلومات مصنع التفريخ والتكلفة')
                    ->schema([
                        SchemaGrid::make(1)
                            ->schema([
                                Select::make('factory_id')
                                    ->label('المفرخ')
                                    ->relationship('factory', 'name', function (Builder $query) {
                                        return $query->where('type', FactoryType::SEEDS);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->helperText('المورد الذي تم شراء الزريعة منه (اختياري - فقط إذا كانت من مفرخة)')
                                    ->columnSpan(1),
                            ]),

                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('initial_quantity')
                                    ->label('الكمية الأولية')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->live(true)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $unitCost = $get('unit_cost');
                                        if ($unitCost && $state) {
                                            $set('total_cost', (float) $unitCost * (int) $state);
                                        }
                                    })
                                    ->helperText('عدد الأسماك/الزريعة عند الإدخال')
                                    ->columnSpan(1),
                                TextInput::make('unit_cost')
                                    ->label('تكلفة الوحدة')
                                    ->numeric()
                                    ->suffix(' EGP ')
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $quantity = $get('initial_quantity');
                                        if ($state && $quantity) {
                                            $set('total_cost', (float) $state * (int) $quantity);
                                        }
                                    })
                                    ->helperText('تكلفة الوحدة الواحدة من المفرخة (بالجنيه المصري)')
                                    ->columnSpan(1),

                                TextInput::make('total_cost')
                                    ->label('التكلفة الإجمالية')
                                    ->numeric()
                                    ->suffix(' EGP ')
                                    ->step(0.001)
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('يتم حسابها تلقائياً (الكمية × تكلفة الوحدة)')
                                    ->columnSpan(1),
                            ]),
                    ]),

                SchemaSection::make('معلومات الكميات والأوزان')
                    ->description('معلومات الكميات والأوزان الحالية والأولية')
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('current_quantity')
                                    ->label('الكمية الحالية')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(fn ($get) => $get('initial_quantity'))
                                    ->helperText('عدد الأسماك/الزريعة الحالي (يبدأ بنفس الكمية الأولية)')
                                    ->columnSpan(1),

                                TextInput::make('initial_weight_avg')
                                    ->label('متوسط الوزن الأولي')
                                    ->numeric()
                                    ->step(0.001)
                                    ->suffix(' جم')
                                    ->minValue(0)
                                    ->helperText('متوسط وزن الوحدة الواحدة عند الإدخال بالجرام')
                                    ->columnSpan(1),
                            ]),

                        TextInput::make('current_weight_avg')
                            ->label('متوسط الوزن الحالي')
                            ->numeric()
                            ->step(0.001)
                            ->suffix(' جم')
                            ->minValue(0)
                            ->helperText('متوسط وزن الوحدة الواحدة الحالي بالجرام (يتم تحديثه مع النمو)')
                            ->columnSpan(1),
                    ]),

                SchemaSection::make('ملاحظات إضافية')
                    ->description('أي معلومات إضافية أو ملاحظات')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('أي ملاحظات إضافية حول الدفعة (اختياري)')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
