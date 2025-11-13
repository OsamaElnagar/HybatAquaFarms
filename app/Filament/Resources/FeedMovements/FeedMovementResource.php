<?php

namespace App\Filament\Resources\FeedMovements;

use App\Filament\Resources\FeedMovements\Pages\CreateFeedMovement;
use App\Filament\Resources\FeedMovements\Pages\EditFeedMovement;
use App\Filament\Resources\FeedMovements\Pages\ListFeedMovements;
use App\Filament\Resources\FeedMovements\Schemas\FeedMovementForm;
use App\Filament\Resources\FeedMovements\Tables\FeedMovementsTable;
use App\Models\FeedMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedMovementResource extends Resource
{
    protected static ?string $model = FeedMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    public static function getNavigationGroup(): ?string
    {
        return 'الأعلاف';
    }

    public static function getNavigationLabel(): string
    {
        return 'حركات الأعلاف';
    }

    public static function getModelLabel(): string
    {
        return 'حركة علف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'حركات الأعلاف';
    }

    public static function form(Schema $schema): Schema
    {
        return FeedMovementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedMovementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedMovements::route('/'),
            'create' => CreateFeedMovement::route('/create'),
            'view' => Pages\ViewFeedMovement::route('/{record}'),
            'edit' => EditFeedMovement::route('/{record}/edit'),
        ];
    }
}
