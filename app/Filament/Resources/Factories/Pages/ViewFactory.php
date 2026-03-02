<?php

namespace App\Filament\Resources\Factories\Pages;

use App\Enums\FactoryType;
use App\Filament\Resources\Factories\FactoryResource;
use App\Filament\Resources\Factories\RelationManagers;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFactory extends ViewRecord
{
    public function getRelationManagers(): array
    {
        $type = $this->getRecord()->type;

        if ($type === FactoryType::FEEDS || $type === FactoryType::SUPPLIER) {
            return [
                RelationManagers\PaymentsRelationManager::class,
            ];
        }

        if ($type === FactoryType::SEEDS) {
            return [
                RelationManagers\BatchesRelationManager::class,
                RelationManagers\BatchPaymentsRelationManager::class,
            ];
        }

        return [];
    }

    protected static string $resource = FactoryResource::class;

    public function getTitle(): string
    {
        return 'عرض: ' . $this->getRecord()->name;
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

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
    protected static bool $isLazy = false;
}
