<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Models\FarmUnit;
use App\Models\MortalityRecord;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
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

class MortalityRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'mortalityRecords';

    protected static ?string $title = 'النفوق';

    protected static ?string $modelLabel = 'نافقة';

    protected static ?string $pluralModelLabel = 'النفوق';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تسجيل النفوق - الدواجن')
                    ->description('سجل حالات النفوق وأسبابها لكل عنبر')
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
                            ->label('عدد النافق')
                            ->required()
                            ->integer()
                            ->minValue(1),

                        TextInput::make('reason')
                            ->label('السبب')
                            ->placeholder('مثلاً: حرارة، مرض، مفترسات')
                            ->required(),

                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->columnSpanFull(),

                        Hidden::make('recorded_by')
                            ->default(fn () => auth()->id()),
                    ])->columns(3)
                    ->columnSpanFull(),
            ]);
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
                    ->label('الوحدة (عنبر/حظيرة)')
                    ->placeholder('غير محدد'),

                TextColumn::make('quantity')
                    ->label('العدد')
                    ->numeric()
                    ->color('danger')
                    ->description(fn (MortalityRecord $record): ?string => $record->reason)
                    ->summarize(Sum::make())
                    ->searchable(query: fn ($query, $search) => $query->where('reason', 'like', "%{$search}%")),

                TextColumn::make('recordedBy.name')
                    ->label('سجل بواسطة')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data) {
                        $ownerRecord = $this->getOwnerRecord();
                        $data['batch_id'] = $ownerRecord->getKey();
                        $data['farm_id'] = $ownerRecord->farm_id ?? $ownerRecord->farm?->id;
                        $data['recorded_by'] = auth()->id();

                        return $this->getRelationship()->create($data);
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->filters([
                //
            ]);
    }
}
