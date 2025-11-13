<?php

namespace App\Filament\Resources\SalaryRecords\Pages;

use App\Filament\Resources\SalaryRecords\SalaryRecordResource;
use App\Filament\Resources\SalaryRecords\Widgets\SalaryRecordsStatsWidget;
use App\Models\Employee;
use App\Models\SalaryRecord;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ListSalaryRecords extends ListRecords
{
    protected static string $resource = SalaryRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('runPayroll')
                ->label('تشغيل كشوف المرتبات')
                ->icon('heroicon-o-banknotes')
                ->modalHeading('تشغيل كشوف المرتبات للفترة')
                ->form([
                    DatePicker::make('from')
                        ->label('من تاريخ')
                        ->required()
                        ->default(now()->startOfMonth())
                        ->displayFormat('Y-m-d')
                        ->native(false),
                    DatePicker::make('to')
                        ->label('إلى تاريخ')
                        ->required()
                        ->default(now()->endOfMonth())
                        ->displayFormat('Y-m-d')
                        ->native(false)
                        ->rule('after_or_equal:from'),
                ])
                ->action(function (array $data): void {
                    $from = Carbon::parse($data['from'])->startOfDay();
                    $to = Carbon::parse($data['to'])->endOfDay();

                    $created = 0;
                    $skipped = 0;

                    DB::transaction(function () use ($from, $to, &$created, &$skipped): void {
                        $employees = Employee::query()
                            ->where('status', 'active')
                            ->get();

                        foreach ($employees as $employee) {
                            $hasOverlap = SalaryRecord::query()
                                ->where('employee_id', $employee->id)
                                ->where(function ($q) use ($from, $to) {
                                    $q->whereBetween('pay_period_start', [$from, $to])
                                      ->orWhereBetween('pay_period_end', [$from, $to])
                                      ->orWhere(function ($qq) use ($from, $to) {
                                          $qq->where('pay_period_start', '<=', $from)
                                             ->where('pay_period_end', '>=', $to);
                                      });
                                })
                                ->exists();

                            if ($hasOverlap) {
                                $skipped++;
                                continue;
                            }

                            $basic = (float) $employee->salary_amount;

                            SalaryRecord::create([
                                'employee_id' => $employee->id,
                                'pay_period_start' => $from->toDateString(),
                                'pay_period_end' => $to->toDateString(),
                                'basic_salary' => $basic,
                                'bonuses' => 0,
                                'deductions' => 0,
                                'advances_deducted' => 0,
                                'net_salary' => $basic,
                                'status' => 'pending',
                                'notes' => 'تم إنشاؤه بواسطة إجراء تشغيل كشوف المرتبات',
                            ]);

                            $created++;
                        }
                    });

                    Notification::make()
                        ->title('تم تشغيل كشوف المرتبات')
                        ->body("تم إنشاء {$created} سجل وتم تخطي {$skipped} لوجود سجلات متداخلة.")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalaryRecordsStatsWidget::class,
        ];
    }
}
