<?php

namespace App\Filament\Resources\Farms\RelationManagers;

use App\Filament\Resources\DailyFeedIssues\Schemas\DailyFeedIssueForm;
use App\Filament\Resources\DailyFeedIssues\Tables\DailyFeedIssuesTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DailyFeedIssuesRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyFeedIssues';

    protected static ?string $title = 'صرف الأعلاف اليومي';

    public function form(Schema $schema): Schema
    {
        return DailyFeedIssueForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return DailyFeedIssuesTable::configure($table)
            ->headerActions([
                CreateAction::make()->label('إضافة صرف علف'),
            ]);
    }
}
