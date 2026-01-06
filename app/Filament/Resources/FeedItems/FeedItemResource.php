<?php

namespace App\Filament\Resources\FeedItems;

use App\Filament\Resources\FeedItems\Pages\CreateFeedItem;
use App\Filament\Resources\FeedItems\Pages\EditFeedItem;
use App\Filament\Resources\FeedItems\Pages\ListFeedItems;
use App\Filament\Resources\FeedItems\Schemas\FeedItemForm;
use App\Filament\Resources\FeedItems\Tables\FeedItemsTable;
use App\Models\FeedItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class FeedItemResource extends Resource
{
    protected static ?string $model = FeedItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    public static function getNavigationGroup(): ?string
    {
        return 'الأعلاف';
    }

    public static function getNavigationLabel(): string
    {
        return 'أصناف الأعلاف';
    }

    public static function getModelLabel(): string
    {
        return 'صنف علف';
    }

    public static function getPluralModelLabel(): string
    {
        return 'أصناف الأعلاف';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name.' - '.$record->code;
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return FeedItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedItemsTable::configure($table);
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
            'index' => ListFeedItems::route('/'),
            'create' => CreateFeedItem::route('/create'),
            'edit' => EditFeedItem::route('/{record}/edit'),
        ];
    }
}
