<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\EmployeeAdvances\Actions\SettleWithExpensesAction;
use App\Filament\Resources\Employees\Actions\MarkDaysOffAction;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Infolists\EmployeeInfolist;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    public function getTitle(): string
    {
        return 'بيانات الموظف: '.$this->getRecord()->name;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            '#' => $this->getRecord()->name,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            MarkDaysOffAction::make(),
            SettleWithExpensesAction::make(),
            Action::make('statement')
                ->label('كشف الحساب')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => EmployeeResource::getUrl('statement', ['record' => $this->getRecord()])),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
