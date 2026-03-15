<?php

namespace App\Filament\Resources\FeedMovements\Schemas;

use App\Enums\FactoryType;
use App\Enums\FeedMovementType;
use App\Filament\Resources\FeedWarehouses\FeedWarehouseResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
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
                            ->options(FeedMovementType::class)
                            ->default(FeedMovementType::In)
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText('ملاحظة: حركات الصرف (صادر) يتم إنشاؤها تلقائياً من خلال الصرف اليومي للأعلاف')
                            ->columnSpan(1),
                        Select::make('factory_id')
                            ->label('المصنع')
                            ->relationship('factory', 'name', function (Builder $query) {
                                return $query->where('type', FactoryType::FEEDS);
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('movement_type') === FeedMovementType::In)
                            ->required(fn (Get $get) => $get('movement_type') === FeedMovementType::In)
                            ->helperText('مطلوب للحركات الواردة')
                            ->columnSpan(1),
                        Select::make('driver_id')
                            ->label('السائق')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('movement_type') === FeedMovementType::In)
                            ->helperText('السائق الذي قام بتسليم العلف للحركات الواردة')
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
                            ->suffix('كجم')
                            ->helperText('الكمية المراد تحريكها')
                            ->columnSpan(1),
                        TextInput::make('total_cost')
                            ->label('إجمالي التكلفة')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('EGP')
                            ->visible(fn (Get $get) => $get('movement_type') === FeedMovementType::In)
                            ->required(fn (Get $get) => $get('movement_type') === FeedMovementType::In)
                            ->helperText('تكلفة الشحنة الواردة بالكامل')
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
                            ->hintAction(
                                Action::make('view_from_warehouse')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->url(fn ($state) => $state ? FeedWarehouseResource::getUrl('view', ['record' => $state]) : null)
                                    ->visible(fn ($state) => filled($state))
                                    ->tooltip('عرض المستودع')
                            )
                            ->visible(fn (Get $get) => $get('movement_type') === FeedMovementType::Transfer)
                            ->required(fn (Get $get) => $get('movement_type') === FeedMovementType::Transfer)
                            ->helperText('مطلوب للحركات النقل (Transfer)')
                            ->columnSpan(1),
                        Select::make('to_warehouse_id')
                            ->label('إلى المستودع')
                            ->relationship('toWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->hintAction(
                                Action::make('view_to_warehouse')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->url(fn ($state) => $state ? FeedWarehouseResource::getUrl('view', ['record' => $state]) : null)
                                    ->visible(fn ($state) => filled($state))
                                    ->tooltip('عرض المستودع')
                            )
                            ->required(fn (Get $get) => $get('movement_type') !== FeedMovementType::Out)
                            ->helperText('مطلوب للحركات الواردة (In) والنقل (Transfer)')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('معلومات إضافية')
                    ->schema([

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
