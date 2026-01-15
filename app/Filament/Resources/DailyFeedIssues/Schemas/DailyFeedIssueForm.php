<?php

namespace App\Filament\Resources\DailyFeedIssues\Schemas;

use App\Models\FarmUnit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DailyFeedIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الموقع وتفاصيل الصرف')
                    ->description('يرجى اختيار المزرعة والوحدة وملء بيانات صرف العلف')
                    ->schema([
                        Select::make('farm_id')
                            ->label('المزرعة')
                            ->relationship('farm', 'name', modifyQueryUsing: fn($query) => $query->active()->latest())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('unit_id', null))
                            ->helperText('يرجى اختيار المزرعة ذات الصلة'),
                        Select::make('unit_id')
                            ->label('الوحدة او الحوض')
                            ->relationship('unit', modifyQueryUsing: fn($query,Get $get) => $query->where('farm_id', $get('farm_id')))
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->code . ' - ' . $record->name)
                            ->searchable()
                            ->preload()
                            ->helperText('اختر وحدة تابعة للمزرعة المختارة (حوض/خزان)'),
                        Select::make('feed_item_id')
                            ->label('صنف العلف')
                            ->relationship('feedItem', 'name')
                            ->required()
                            ->helperText('اختر صنف العلف المصروف'),
                        Select::make('feed_warehouse_id')
                            ->label('مخزن العلف')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->helperText('حدد المخزن الذي تم صرف العلف منه'),
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->required()
                            ->maxDate(now()->tomorrow())
                            ->helperText('تاريخ عملية الصرف'),
                        TextInput::make('quantity')
                            ->label('الكمية (كجم)')
                            ->required()
                            ->numeric()
                            ->helperText('أدخل كمية العلف المصروف بالكيلو جرام'),
                        Select::make('batch_id')
                            ->label('دفعة الزريعة')
                            ->relationship('batch', 'batch_code', modifyQueryUsing: fn($query) => $query->latest())
                            ->helperText('حدد دفعة الزريعة إذا كانت موجودة'),
                    ])
                    ->columns(2)->columnSpanFull(),

                Section::make('إضافات وملاحظات')
                    ->description('معلومات المستخدم والملاحظات الإضافية')
                    ->schema([
                        Select::make('recorded_by')
                            ->label('سجل بواسطة')
                            ->relationship('recordedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn() => auth('web')->id())
                            ->helperText('المستخدم الذي قام بتسجيل عملية الصرف'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull()
                            ->maxLength(1000)
                            ->helperText('أضف أية ملاحظات إضافية متعلقة بالصرف'),
                    ])
                    ->columns(1)->columnSpanFull(),
            ]);
    }
}
