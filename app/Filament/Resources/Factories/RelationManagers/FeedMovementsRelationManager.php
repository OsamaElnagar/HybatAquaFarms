<?php

namespace App\Filament\Resources\Factories\RelationManagers;

use App\Filament\Resources\FeedMovements\Schemas\FeedMovementForm;
use App\Filament\Resources\FeedMovements\Tables\FeedMovementsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FeedMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'feedMovements';

    protected static ?string $title = 'حركة الاعلاف';

    protected static ?string $label = 'حركة علف';

    protected static ?string $pluralLabel = 'حركات الاعلاف';

    public function form(Schema $schema): Schema
    {
        return FeedMovementForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return FeedMovementsTable::configure($table);
    }
}
