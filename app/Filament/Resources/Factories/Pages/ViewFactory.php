<?php

namespace App\Filament\Resources\Factories\Pages;

use App\Enums\FactoryType;
use App\Filament\Resources\Factories\FactoryResource;
use App\Filament\Resources\Factories\RelationManagers;
use App\Filament\Resources\Factories\Widgets\FactoryActivityWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFactory extends ViewRecord
{
    public function getRelationManagers(): array
    {
        $type = $this->getRecord()->type;

        if ($type === FactoryType::SUPPLIER) {
            return [
                RelationManagers\PaymentsRelationManager::class,
                RelationManagers\PartnerLoansRelationManager::class,
            ];
        }

        if ($type === FactoryType::FEEDS) {
            return [
                RelationManagers\PaymentsRelationManager::class,
                RelationManagers\FeedMovementsRelationManager::class,
                RelationManagers\PartnerLoansRelationManager::class,

            ];
        }

        if ($type === FactoryType::SEEDS) {
            return [
                RelationManagers\BatchesRelationManager::class,
                RelationManagers\BatchPaymentsRelationManager::class,
                RelationManagers\PartnerLoansRelationManager::class,

            ];
        }

        return [];
    }

    protected static string $resource = FactoryResource::class;

    public function getTitle(): string
    {
        return 'عرض: '.$this->getRecord()->name;
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
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FactoryActivityWidget::class,
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected static bool $isLazy = false;
}
