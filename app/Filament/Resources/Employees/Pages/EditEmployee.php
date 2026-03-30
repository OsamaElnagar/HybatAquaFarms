<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\EmployeeAdvances\Actions\SettleWithExpensesAction;
use App\Filament\Resources\Employees\Actions\MarkDaysOffAction;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    public function getTitle(): string
    {
        return 'تعديل بيانات موظف: '.$this->getRecord()->name;
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
            MarkDaysOffAction::make(),
            SettleWithExpensesAction::make(),
            Action::make('statement')
                ->label('كشف الحساب')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => EmployeeResource::getUrl('statement', ['record' => $this->getRecord()])),

            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
