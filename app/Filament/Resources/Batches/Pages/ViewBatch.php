<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\Batches\Infolists\BatchInfolist;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

    public function getTitle(): string
    {
        return 'تفاصيل دورة: '.$this->getRecord()->batch_code;
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        return [
            $resource::getUrl('index') => $resource::getBreadcrumb(),
            '#' => $this->getRecord()->batch_code,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close_cycle')
                ->label('إقفال الدورة')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(
                    fn ($record) => ! $record->is_cycle_closed &&
                    $record->status->value === 'harvested',
                )
                ->url(fn ($record) => BatchResource::getUrl('close', ['record' => $record])),

            Action::make('reopen_cycle')
                ->label('إعادة فتح الدورة')
                ->icon('heroicon-o-lock-open')
                ->color('danger')
                ->visible(fn ($record) => $record->is_cycle_closed)
                ->requiresConfirmation()
                ->modalHeading('إعادة فتح دورة الإنتاج')
                ->modalDescription(
                    'هل أنت متأكد من إعادة فتح هذه الدورة؟ سيتم إلغاء البيانات المالية المحفوظة.',
                )
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->action(function ($record) {
                    $record->update([
                        'is_cycle_closed' => false,
                        'closure_date' => null,
                        'total_feed_cost' => null,
                        'total_operating_expenses' => null,
                        'total_revenue' => null,
                        'net_profit' => null,
                        'closed_by' => null,
                        'closure_notes' => null,
                    ]);

                    Notification::make()
                        ->title('تم إعادة فتح الدورة')
                        ->warning()
                        ->body(
                            "تم إعادة فتح دورة {$record->batch_code}. يمكنك الآن تعديل البيانات.",
                        )
                        ->send();
                }),

            EditAction::make()
                ->disabled(fn ($record) => $record->is_cycle_closed)
                ->tooltip(
                    fn ($record) => $record->is_cycle_closed
                    ? 'لا يمكن تعديل دورة مقفلة'
                    : null,
                ),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return BatchInfolist::configure($schema);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
