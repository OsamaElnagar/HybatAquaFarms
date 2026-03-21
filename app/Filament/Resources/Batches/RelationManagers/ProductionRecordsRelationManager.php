<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Models\FarmUnit;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductionRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'productionRecords';

    protected static ?string $title = 'إنتاج البيض';

    protected static ?string $modelLabel = 'إنتاج بيض';

    protected static ?string $pluralModelLabel = 'إنتاج البيض';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('إنتاج البيض - الدواجن')
                    ->description('سجل كميات البيض المنتج يومياً لكل عنبر')
                    ->schema([
                        DatePicker::make('date')
                            ->label('التاريخ')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Hidden::make('batch_id'),

                        Hidden::make('farm_id'),

                        Select::make('unit_id')
                            ->label('الوحدة (عنبر/حوض)')
                            ->options(function () {
                                $batchId = $this->getOwnerRecord()?->getKey();
                                if (! $batchId) {
                                    return [];
                                }

                                return FarmUnit::query()
                                    ->whereHas('batches', fn ($q) => $q->where('batches.id', $batchId))
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload(),

                        TextInput::make('quantity')
                            ->label('الكمية المنتجة')
                            ->required()
                            ->numeric()
                            ->minValue(0),

                        Select::make('unit')
                            ->label('الوحدة المستخدمة')
                            ->options([
                                'egg' => 'بيضة',
                                'tray' => 'طبق',
                                'box' => 'صندوق',
                                'kg' => 'كجم',
                            ])
                            ->default('egg')
                            ->required(),

                        TextInput::make('weight')
                            ->label('الوزن الإجمالي (اختياري)')
                            ->numeric()
                            ->suffix('كجم'),

                        TextInput::make('quality_grade')
                            ->label('درجة الجودة / التصنيف')
                            ->placeholder('مثلاً: نمرة 1، صغير، كبير'),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),

                        Hidden::make('recorded_by')
                            ->default(fn () => auth()->id()),
                    ])->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['batch_id'] = $this->getOwnerRecord()->getKey();
        $data['farm_id'] = $this->getOwnerRecord()->farm_id;

        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),

                TextColumn::make('unit.name')
                    ->label('الوحدة')
                    ->placeholder('غير محدد'),

                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->summarize(Sum::make()),

                TextColumn::make('unit')
                    ->label('الوحدة')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'egg' => 'بيضة',
                        'tray' => 'طبق',
                        'box' => 'صندوق',
                        'kg' => 'كجم',
                        default => $state,
                    }),

                TextColumn::make('weight')
                    ->label('الوزن')
                    ->numeric()
                    ->suffix(' كجم')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('quality_grade')
                    ->label('الجودة')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->label('سجل إنتاج بيض'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->filters([
                //
            ]);
    }
}
