<?php

namespace App\Filament\Resources\Batches\RelationManagers;

use App\Filament\Resources\DailyFeedIssues\Schemas\DailyFeedIssueForm;
use App\Filament\Resources\DailyFeedIssues\Tables\DailyFeedIssuesTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DailyFeedIssuesRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyFeedIssues';

    // protected static ?string $title = 'صرف الأعلاف اليومى';
    protected static ?string $pluralModelLabel = 'مصروفات الأعلاف اليومية';

    protected static ?string $modelLabel = 'مصروف علف جديد';

    public function isReadOnly(): bool
    {
        return $this->ownerRecord->is_cycle_closed;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->is_cycle_closed ? 'مصروفات الأعلاف اليومية' : 'صرف الأعلاف اليومى';
    }

    public function form(Schema $schema): Schema
    {
        return DailyFeedIssueForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return DailyFeedIssuesTable::configure($table)
            ->headerActions([
                CreateAction::make()->label('سجًل مصروف علف جديد'),
            ]);
    }
}
