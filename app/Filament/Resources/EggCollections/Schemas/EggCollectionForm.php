<?php

namespace App\Filament\Resources\EggCollections\Schemas;

use App\Models\Batch;
use App\Models\Farm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EggCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        $livewire = $schema->getLivewire();
        $ownerRecord = ($livewire instanceof RelationManager) ? $livewire->getOwnerRecord() : null;
        $isBatchManager = $ownerRecord instanceof Batch;
        $isFarmManager = $ownerRecord instanceof Farm;

        return $schema
            ->components([

                TextInput::make('collection_number')
                    ->label('رقم التجميع')
                    ->disabled()
                    ->dehydrated(),

                Select::make('batch_id')
                    ->label('الدفعة')
                    ->relationship('batch', 'batch_code', modifyQueryUsing: fn ($query) => $query->where('cycle_type', 'poultry'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->default($isBatchManager ? $ownerRecord->id : null)
                    ->disabled($isBatchManager)
                    ->dehydrated()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            $batch = Batch::find($state);
                            if ($batch) {
                                $set('farm_id', $batch->farm_id);
                            }
                        }
                    }),

                Select::make('farm_id')
                    ->label('المزرعة')
                    ->relationship('farm', 'name')
                    ->required()
                    ->default(function () use ($isBatchManager, $isFarmManager, $ownerRecord) {
                        if ($isBatchManager) {
                            return $ownerRecord->farm_id;
                        }
                        if ($isFarmManager) {
                            return $ownerRecord->id;
                        }

                        return null;
                    })
                    ->disabled($isBatchManager || $isFarmManager)
                    ->dehydrated(),

                DatePicker::make('collection_date')
                    ->label('تاريخ التجميع')
                    ->default(now())
                    ->required()
                    ->native(false),

                TextInput::make('total_trays')
                    ->label('عدد الصناديق')
                    ->required()
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $trays = (int) ($state ?? 0);
                        $set('total_eggs', $trays * 30);
                    }),

                TextInput::make('total_eggs')
                    ->label('عدد البيض')
                    ->required()
                    ->numeric()
                    ->default(function (Get $get) {
                        return (int) ($get('total_trays') ?? 0) * 30;
                    }),

                TextInput::make('quality_grade')
                    ->label('درجة الجودة')
                    ->placeholder('نمرة 1، نمرة 2، صغير'),

                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
