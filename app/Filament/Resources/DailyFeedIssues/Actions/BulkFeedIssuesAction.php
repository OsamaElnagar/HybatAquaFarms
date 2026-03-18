<?php

namespace App\Filament\Resources\DailyFeedIssues\Actions;

use App\Filament\Resources\DailyFeedIssues\Schemas\DailyFeedIssueForm;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class BulkFeedIssuesAction extends CreateAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulkFeedIssues';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('صرف علف متعدد')
            ->modalHeading('صرف أعلاف لعدة أحواض/دفعات')
            ->modalWidth('7xl')
            ->schema([
                Repeater::make('issues')
                    ->label('المنصرف')
                    ->schema(fn (Schema $schema) => DailyFeedIssueForm::configure($schema->livewire($this->getLivewire())))
                    ->minItems(1)
                    ->reorderable()
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $livewire = $this->getLivewire();
                $lastDate = null;

                foreach ($data['issues'] ?? [] as $issueData) {
                    $livewire->getRelationship()->create($issueData);
                    $lastDate = $issueData['date'];
                }

                if ($lastDate) {
                    Cache::put('user_'.auth('web')->id().'_last_feed_issue_date', $lastDate, now()->addDays(1));
                }
            });
    }
}
