<?php

namespace App\Filament\Resources\BatchMovements\Pages;

use App\Enums\MovementType;
use App\Filament\Resources\BatchMovements\BatchMovementResource;
use App\Filament\Resources\BatchMovements\Widgets\BatchMovementsStatsWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBatchMovements extends ListRecords
{
    protected static string $resource = BatchMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('quickMortality')
                ->label('نفوق سريع')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->schema([
                    Select::make('batch_id')
                        ->label('الدفعة')
                        ->relationship('batch', 'batch_code')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('quantity')
                        ->label('الكمية')
                        ->required()
                        ->numeric()
                        ->minValue(1),
                    TextInput::make('weight')
                        ->label('الوزن (كجم)')
                        ->numeric()
                        ->step(0.001)
                        ->suffix(' كجم'),
                    DatePicker::make('date')
                        ->label('التاريخ')
                        ->required()
                        ->default(now())
                        ->displayFormat('Y-m-d')
                        ->native(false),
                    TextInput::make('reason')
                        ->label('السبب')
                        ->maxLength(255)
                        ->default('نفوق'),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    \App\Models\BatchMovement::create([
                        'batch_id' => $data['batch_id'],
                        'movement_type' => MovementType::Mortality,
                        'quantity' => $data['quantity'],
                        'weight' => $data['weight'] ?? null,
                        'date' => $data['date'],
                        'reason' => $data['reason'] ?? 'نفوق',
                        'notes' => $data['notes'] ?? null,
                        'recorded_by' => Auth::id(),
                    ]);
                })
                ->successNotificationTitle('تم تسجيل النفوق بنجاح'),

            Action::make('quickHarvest')
                ->label('حصاد سريع')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->schema([
                    Select::make('batch_id')
                        ->label('الدفعة')
                        ->relationship('batch', 'batch_code')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('quantity')
                        ->label('الكمية')
                        ->required()
                        ->numeric()
                        ->minValue(1),
                    TextInput::make('weight')
                        ->label('الوزن (كجم)')
                        ->numeric()
                        ->step(0.001)
                        ->suffix(' كجم'),
                    DatePicker::make('date')
                        ->label('التاريخ')
                        ->required()
                        ->default(now())
                        ->displayFormat('Y-m-d')
                        ->native(false),
                    TextInput::make('reason')
                        ->label('السبب')
                        ->maxLength(255)
                        ->default('حصاد'),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    \App\Models\BatchMovement::create([
                        'batch_id' => $data['batch_id'],
                        'movement_type' => MovementType::Harvest,
                        'quantity' => $data['quantity'],
                        'weight' => $data['weight'] ?? null,
                        'date' => $data['date'],
                        'reason' => $data['reason'] ?? 'حصاد',
                        'notes' => $data['notes'] ?? null,
                        'recorded_by' => Auth::id(),
                    ]);
                })
                ->successNotificationTitle('تم تسجيل الحصاد بنجاح'),

            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BatchMovementsStatsWidget::class,
        ];
    }

    // tabs
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('الكل'),
        ];

        foreach (MovementType::cases() as $movementType) {
            $tabs[$movementType->value] = Tab::make($movementType->getLabel())->modifyQueryUsing(function (Builder $query) use ($movementType) {
                return $query->where('movement_type', $movementType->value);
            });
        }

        return $tabs;
    }
}
