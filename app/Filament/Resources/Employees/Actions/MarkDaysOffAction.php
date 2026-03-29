<?php

namespace App\Filament\Resources\Employees\Actions;

use App\Models\Employee;
use App\Models\EmployeeDayOff;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class MarkDaysOffAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'markDaysOff';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('تسجيل غياب')
            ->icon('heroicon-o-calendar-days')
            ->color('danger')
            ->form([
                DatePicker::make('date_from')
                    ->label('من تاريخ')
                    ->required()
                    ->default(now()->subDay())
                    ->native(false)
                    ->displayFormat('Y-m-d'),
                DatePicker::make('date_to')
                    ->label('إلى تاريخ')
                    ->required()
                    ->default(now())
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->afterOrEqual('date_from'),
                TextInput::make('reason')
                    ->label('السبب')
                    ->placeholder('مثال: إجازة مرضية، غياب، إجازة شخصية')
                    ->maxLength(255),
            ])
            ->action(function (array $data, Employee $record) {
                $period = CarbonPeriod::create($data['date_from'], $data['date_to']);
                $created = 0;
                $skipped = 0;

                foreach ($period as $date) {
                    $exists = EmployeeDayOff::where('employee_id', $record->id)
                        ->where('date', $date->toDateString())
                        ->exists();

                    if ($exists) {
                        $skipped++;

                        continue;
                    }

                    EmployeeDayOff::create([
                        'employee_id' => $record->id,
                        'date' => $date->toDateString(),
                        'reason' => $data['reason'] ?? null,
                    ]);

                    $created++;
                }

                $message = "تم تسجيل {$created} يوم غياب";
                if ($skipped > 0) {
                    $message .= " (تم تخطي {$skipped} يوم مسجل مسبقاً)";
                }

                Notification::make()
                    ->title($message)
                    ->success()
                    ->send();
            })
            ->slideOver();
    }
}
