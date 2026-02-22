<?php

namespace App\Filament\Resources\Batches\Schemas;

use App\Enums\BatchStatus;
use App\Enums\FactoryType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                        SchemaGrid::make(3)
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

                                Select::make('status')
                                    ->label('الحالة')
                                    ->options(BatchStatus::class)
                                    ->default('active')
                                    ->required()
                                    ->native(false)
                                    ->helperText('حالة الدفعة: نشط، محصود، مستنفذ')
                                    ->columnSpan(1),
                            ]),

                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('farm_id')
                                    ->label('المزرعة')
                                    ->default(fn($livewire) => $livewire instanceof RelationManager ? $livewire->getOwnerRecord()->getKey() : null)
                                    ->disabled(fn($livewire) => $livewire instanceof RelationManager)
                                    ->relationship('farm', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn(callable $set) => $set('units', []))
                                    ->helperText('اختر المزرعة التي سيتم إدخال الزريعة فيها')
                                    ->columnSpan(1),

                                Select::make('units')
                                    ->label('الوحدات')
                                    ->relationship('units', 'name', function (Builder $query, callable $get) {
                                        $farmId = $get('farm_id');
                                        if (!$farmId) {
                                            return $query->whereRaw('1=0');
                                        }

                                        return $query->where('farm_id', $farmId);
                                    })
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->helperText('اختر الوحدات (الأحواض/الخزانات)')
                                    ->columnSpan(1),
                            ]),
                    ])->columnSpanFull(),

                SchemaSection::make('ملخص الإجماليات')
                    ->description('إجمالي الكميات والتكاليف المحسوبة من سجلات الأسماك')
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('initial_quantity')
                                    ->label('إجمالي الكمية الأولية')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                TextInput::make('total_cost')
                                    ->label('إجمالي التكلفة')
                                    ->numeric()
                                    ->suffix(' EGP ')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),

                                TextInput::make('current_quantity')
                                    ->label('إجمالي الكمية الحالية')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->hiddenOn('create'),
            ]);
    }
}
