<?php

namespace App\Filament\Resources\FeedMovements\Schemas;

use App\Enums\FeedMovementType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FeedMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        Select::make('movement_type')
                            ->label('نوع الحركة')
                            ->options(function () {
                                // Only allow In and Transfer types - Out movements are created automatically via DailyFeedIssue
                                return collect(FeedMovementType::cases())
                                    ->filter(fn ($type) => $type !== FeedMovementType::Out)
                                    ->mapWithKeys(fn ($type) => [$type->value => $type->getLabel()])
                                    ->toArray();
                            })
                            ->required()
                            ->native(false)
                            ->helperText('ملاحظة: حركات الصرف (Out) يتم إنشاؤها تلقائياً من خلال الصرف اليومي للأعلاف')
                            ->columnSpan(1),
                        Select::make('feed_item_id')
                            ->label('صنف العلف')
                            ->relationship('feedItem', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('نوع العلف المراد تحريكه')
                            ->columnSpan(1),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->default(now())
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->helperText('تاريخ الحركة')
                            ->columnSpan(1),
                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->required()
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->helperText('الكمية المراد تحريكها')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('المستودعات')
                    ->schema([
                        Select::make('from_warehouse_id')
                            ->label('من المستودع')
                            ->relationship('fromWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => in_array($get('movement_type'), ['transfer']))
                            ->required(fn ($get) => $get('movement_type') === 'transfer')
                            ->helperText('مطلوب للحركات النقل (Transfer)')
                            ->columnSpan(1),
                        Select::make('to_warehouse_id')
                            ->label('إلى المستودع')
                            ->relationship('toWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => in_array($get('movement_type'), ['in', 'transfer']))
                            ->required(fn ($get) => in_array($get('movement_type'), ['in', 'transfer']))
                            ->helperText('مطلوب للحركات الواردة (In) والنقل (Transfer)')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('معلومات إضافية')
                    ->schema([
                        Select::make('factory_id')
                            ->label('المصنع')
                            ->relationship('factory', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('movement_type') === 'in')
                            ->required(fn ($get) => $get('movement_type') === 'in')
                            ->helperText('مطلوب للحركات الواردة (In)')
                            ->columnSpan(1),
                        Select::make('recorded_by')
                            ->label('سجل بواسطة')
                            ->relationship('recordedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => Auth::id())
                            ->helperText('المستخدم الذي قام بتسجيل الحركة')
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('أي ملاحظات إضافية حول الحركة')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
