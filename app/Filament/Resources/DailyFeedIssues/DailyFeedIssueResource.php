<?php

namespace App\Filament\Resources\DailyFeedIssues;

use App\Filament\Resources\DailyFeedIssues\Pages\CreateDailyFeedIssue;
use App\Filament\Resources\DailyFeedIssues\Pages\EditDailyFeedIssue;
use App\Filament\Resources\DailyFeedIssues\Pages\ListDailyFeedIssues;
use App\Filament\Resources\DailyFeedIssues\Schemas\DailyFeedIssueForm;
use App\Filament\Resources\DailyFeedIssues\Tables\DailyFeedIssuesTable;
use App\Models\DailyFeedIssue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DailyFeedIssueResource extends Resource
{
    protected static ?string $model = DailyFeedIssue::class;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['farm', 'unit', 'feedItem', 'warehouse', 'batch', 'recordedBy']);
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function getNavigationGroup(): ?string
    {
        return 'الأعلاف';
    }

    public static function getNavigationLabel(): string
    {
        return 'الصرف اليومي للأعلاف';
    }

    public static function getModelLabel(): string
    {
        return 'صرف يومي';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الصرف اليومي للأعلاف';
    }

    public static function form(Schema $schema): Schema
    {
        return DailyFeedIssueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyFeedIssuesTable::configure($table);
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
            'index' => ListDailyFeedIssues::route('/'),
            // 'create' => CreateDailyFeedIssue::route('/create'),
            'view' => Pages\ViewDailyFeedIssue::route('/{record}'),
            // 'edit' => EditDailyFeedIssue::route('/{record}/edit'),
        ];
    }
}
