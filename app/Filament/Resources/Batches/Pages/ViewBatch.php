<?php

namespace App\Filament\Resources\Batches\Pages;

use App\Filament\Resources\Batches\BatchResource;
use App\Filament\Resources\Batches\Infolists\BatchInfolist;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewBatch extends ViewRecord
{
    protected static string $resource = BatchResource::class;

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
                ->requiresConfirmation()
                ->modalHeading('إقفال دورة الإنتاج')
                ->modalDescription(
                    'سيتم حساب جميع التكاليف والإيرادات وإقفال الدورة. لن تتمكن من التعديل بعد الإقفال.',
                )
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->schema([
                    DatePicker::make('closure_date')
                        ->label('تاريخ الإقفال')
                        ->default(now())
                        ->required()
                        ->native(false)
                        ->maxDate(now()),

                    Textarea::make('closure_notes')
                        ->label('ملاحظات الإقفال')
                        ->rows(3)
                        ->placeholder('أضف أي ملاحظات حول إقفال هذه الدورة...')
                        ->columnSpanFull(),
                ])
                ->action(function ($record, array $data) {
                    // Calculate and freeze all financials
                    $record->update([
                        'is_cycle_closed' => true,
                        'closure_date' => $data['closure_date'],
                        'total_feed_cost' => $record->total_feed_cost,
                        'total_operating_expenses' => $record->allocated_expenses,
                        'total_revenue' => $record->total_revenue,
                        'net_profit' => $record->net_profit,
                        'closed_by' => auth('web')->id(),
                        'closure_notes' => $data['closure_notes'] ?? null,
                    ]);

                    Notification::make()
                        ->title('تم إقفال الدورة بنجاح')
                        ->success()
                        ->body(
                            "تم إقفال دورة {$record->batch_code} بصافي ربح: ".
                                number_format($record->net_profit).
                                ' EGP',
                        )
                        ->send();
                }),

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
}
