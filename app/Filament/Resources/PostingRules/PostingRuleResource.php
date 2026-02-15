<?php

namespace App\Filament\Resources\PostingRules;

use App\Filament\Resources\PostingRules\Pages\CreatePostingRule;
use App\Filament\Resources\PostingRules\Pages\EditPostingRule;
use App\Filament\Resources\PostingRules\Pages\ListPostingRules;
use App\Filament\Resources\PostingRules\Schemas\PostingRuleForm;
use App\Filament\Resources\PostingRules\Tables\PostingRulesTable;
use App\Models\PostingRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostingRuleResource extends Resource
{
    protected static ?string $model = PostingRule::class;

    protected static bool $shouldRegisterRoute = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة و المالية';
    }

    public static function getNavigationLabel(): string
    {
        return 'قواعد الترحيل';
    }

    public static function getModelLabel(): string
    {
        return 'قاعدة ترحيل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'قواعد الترحيل';
    }

    public static function form(Schema $schema): Schema
    {
        return PostingRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostingRulesTable::configure($table);
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
            'index' => ListPostingRules::route('/'),
            'create' => CreatePostingRule::route('/create'),
            'edit' => EditPostingRule::route('/{record}/edit'),
        ];
    }
}
