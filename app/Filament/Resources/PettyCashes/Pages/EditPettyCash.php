<?php

namespace App\Filament\Resources\PettyCashes\Pages;

use App\Filament\Resources\PettyCashes\PettyCashResource;
use App\Filament\Resources\PettyCashes\Widgets\PettyCashStatsWidget;
use App\Models\PettyCashTransaction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPettyCash extends EditRecord
{
    protected static string $resource = PettyCashResource::class;

    public function getTitle(): string
    {
        return 'تعديل العهدة: '.$this->getRecord()->name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            $resource::getUrl('view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            '#' => 'تعديل',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('replenish')
            //     ->label('تزويد سريع')
            //     ->icon('heroicon-o-plus-circle')
            //     ->color('success')
            //     ->schema([
            //         DatePicker::make('date')
            //             ->label('التاريخ')
            //             ->required()
            //             ->default(now()),
            //         TextInput::make('amount')
            //             ->label('المبلغ')
            //             ->required()
            //             ->numeric()
            //             ->suffix(' EGP ')
            //             ->minValue(0.01)
            //             ->step(0.01),
            //         Textarea::make('description')
            //             ->label('الوصف')
            //             ->required()
            //             ->default('تزويد العهدة')
            //             ->maxLength(500),
            //     ])
            //     ->action(function (array $data): void {
            //         PettyCashTransaction::create([
            //             'petty_cash_id' => $this->record->id,
            //             'date' => $data['date'],
            //             'direction' => 'in',
            //             'amount' => $data['amount'],
            //             'description' => $data['description'],
            //             'recorded_by' => Auth::id(),
            //         ]);

            //         $this->dispatch('replenishment-created');
            //     })
            //     ->successNotificationTitle('تم التزويد بنجاح')
            //     ->modalHeading('تزويد العهدة')
            //     ->modalSubmitActionLabel('تزويد'),
            DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PettyCashStatsWidget::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
