<?php

namespace App\Filament\Resources\HarvestOperations\Schemas;

use App\Enums\HarvestOperationStatus;
use App\Models\Batch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HarvestOperationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات العملية')
                    ->schema([
                        TextInput::make('operation_number')
                            ->label('رقم العملية')
                            ->default(fn () => 'HOP-'.str_pad(((\App\Models\HarvestOperation::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('الحالة')
                            ->options(HarvestOperationStatus::class)
                            ->default(HarvestOperationStatus::Planned->value)
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                    ])->columns(2),

                Section::make('تفاصيل الدفعة والمزرعة')
                    ->schema([
                        Select::make('batch_id')
                            ->label('الدفعة')
                            ->relationship('batch', 'batch_code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?int $state) {
                                if ($state) {
                                    $batch = Batch::find($state);
                                    if ($batch) {
                                        $set('farm_id', $batch->farm_id);
                                    }
                                }
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->batch_code} - {$record->species->name}")
                            ->columnSpan(1),

                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                    ])->columns(2),

                Section::make('المدة الزمنية')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $endDate = $get('end_date');
                                if ($state && $endDate) {
                                    $days = \Carbon\Carbon::parse($state)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
                                    $set('estimated_duration_days', $days);
                                }
                            })
                            ->columnSpan(1),

                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->native(false)
                            ->after('start_date')
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $startDate = $get('start_date');
                                if ($state && $startDate) {
                                    $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($state)) + 1;
                                    $set('estimated_duration_days', $days);
                                }
                            })
                            ->columnSpan(1),

                        TextInput::make('estimated_duration_days')
                            ->label('المدة المتوقعة (أيام)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->suffix('يوم')
                            ->columnSpan(1),
                    ])->columns(3),

                Section::make('ملاحظات إضافية')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }
}
