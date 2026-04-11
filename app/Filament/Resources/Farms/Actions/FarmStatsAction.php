<?php

namespace App\Filament\Resources\Farms\Actions;

use App\Jobs\GenerateFarmStatsReport;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\FarmReport;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class FarmStatsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'farmStats';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('إحصائيات المزرعة')
            ->icon('heroicon-o-chart-bar')
            ->color('info')
            ->form([
                Section::make('الفلترة')
                    ->schema([
                        Checkbox::make('annual_basis')
                            ->label('أساس سنوي (السنة الحالية)')
                            ->default(true)
                            ->live(),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('من تاريخ')
                                    ->hidden(fn (Get $get) => $get('annual_basis')),
                                DatePicker::make('end_date')
                                    ->label('إلى تاريخ')
                                    ->hidden(fn (Get $get) => $get('annual_basis')),
                            ]),
                        Select::make('batch_ids')
                            ->label('اختيار دفعات محددة (اختياري)')
                            ->multiple()
                            ->options(fn (Farm $record) => Batch::where('farm_id', $record->id)
                                ->latest()
                                ->pluck('batch_code', 'id'))
                            ->searchable()
                            ->placeholder('اترك فارغاً لاستخدام فلاتر التاريخ'),
                    ]),
            ])
            ->action(function (array $data, Farm $record) {
                $report = FarmReport::create([
                    'farm_id' => $record->id,
                    'user_id' => auth()->id(),
                    'filters' => $data,
                    'status' => 'pending',
                ]);

                GenerateFarmStatsReport::dispatch($report->id);

                Notification::make()
                    ->info()
                    ->title('جاري إنشاء التقرير')
                    ->body('يتم الآن حساب الإحصائيات في الخلفية. ستتلقى تنبيهاً فور الجاهزية.')
                    ->send();
            });
    }
}
