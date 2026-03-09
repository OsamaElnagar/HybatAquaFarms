<?php

namespace App\Filament\Resources\Factories\Pages;

use App\Enums\FactoryType;
use App\Filament\Resources\Factories\FactoryResource;
use App\Filament\Resources\Factories\RelationManagers;
use App\Filament\Resources\Factories\Widgets\FactoryActivityWidget;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFactory extends EditRecord
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
                RelationManagers\FeedMovementsRelationManager::class,
                RelationManagers\PaymentsRelationManager::class,
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
        return 'تعديل: ' . $this->getRecord()->name;
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
            DeleteAction::make(),
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
