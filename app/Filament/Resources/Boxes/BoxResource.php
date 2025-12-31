<?php

namespace App\Filament\Resources\Boxes;

use App\Filament\Resources\Boxes\Pages\CreateBox;
use App\Filament\Resources\Boxes\Pages\EditBox;
use App\Filament\Resources\Boxes\Pages\ListBoxes;
use App\Filament\Resources\Boxes\Schemas\BoxForm;
use App\Filament\Resources\Boxes\Tables\BoxesTable;
use App\Models\Box;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BoxResource extends Resource
{
    protected static ?string $model = Box::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return 'الحصاد والمبيعات';
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'بوكسه';

    protected static ?string $pluralModelLabel = 'صمم بوكسه';

    public static function form(Schema $schema): Schema
    {
        return BoxForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoxesTable::configure($table);
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
            'index' => ListBoxes::route('/'),
            'create' => CreateBox::route('/create'),
            'edit' => EditBox::route('/{record}/edit'),
        ];
    }
}
