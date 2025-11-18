<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Infolists\EmployeeInfolist;
use App\Models\SalaryRecord;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            // Action::make('createSalaryForPeriod')
            //     ->label('إنشاء مرتب لفترة')
            //     ->icon('heroicon-o-banknotes')
            //     ->modalHeading('حساب مرتب حسب مدة التواجد')
            //     ->form([
            //         DatePicker::make('from')
            //             ->label('من تاريخ')
            //             ->required()
            //             ->default(now()->startOfMonth())
            //             ->displayFormat('Y-m-d')
            //             ->native(false),
            //         DatePicker::make('to')
            //             ->label('إلى تاريخ')
            //             ->required()
            //             ->default(now()->endOfMonth())
            //             ->displayFormat('Y-m-d')
            //             ->native(false)
            //             ->rule('after_or_equal:from'),
            //         TextInput::make('unpaid_days')
            //             ->label('أيام غير مدفوعة')
            //             ->numeric()
            //             ->default(0)
            //             ->minValue(0)
            //             ->step(1),
            //     ])
            //     ->action(function (array $data): void {
            //         $employee = $this->getRecord();
            //         $from = Carbon::parse($data['from'])->startOfDay();
            //         $to = Carbon::parse($data['to'])->startOfDay();
            //         $unpaid = (int) ($data['unpaid_days'] ?? 0);

            //         // Overlap check
            //         $hasOverlap = SalaryRecord::query()
            //             ->where('employee_id', $employee->id)
            //             ->where(function ($q) use ($from, $to) {
            //                 $q->whereBetween('pay_period_start', [$from, $to])
            //                     ->orWhereBetween('pay_period_end', [$from, $to])
            //                     ->orWhere(function ($qq) use ($from, $to) {
            //                         $qq->where('pay_period_start', '<=', $from)
            //                             ->where('pay_period_end', '>=', $to);
            //                     });
            //             })
            //             ->exists();

            //         if ($hasOverlap) {
            //             Notification::make()
            //                 ->title('تعذر الإنشاء')
            //                 ->body('يوجد سجل مرتب آخر يتداخل مع هذه الفترة لهذا الموظف.')
            //                 ->warning()
            //                 ->send();

            //             return;
            //         }

            //         DB::transaction(function () use ($employee, $from, $to, $unpaid): void {
            //             $perDay = (float) $employee->basic_salary / 26;
            //             $days = max($from->diffInDays($to) + 1 - $unpaid, 0);
            //             $basic = max(round($perDay * $days, 2), 0);

            //             SalaryRecord::create([
            //                 'employee_id' => $employee->id,
            //                 'pay_period_start' => $from->toDateString(),
            //                 'pay_period_end' => $to->toDateString(),
            //                 'basic_salary' => $basic,
            //                 'bonuses' => 0,
            //                 'deductions' => 0,
            //                 'advances_deducted' => 0,
            //                 'net_salary' => $basic,
            //                 'status' => 'pending',
            //                 'notes' => 'تم إنشاؤه من صفحة الموظف (حساب حسب مدة التواجد).',
            //             ]);
            //         });

            //         Notification::make()
            //             ->title('تم إنشاء سجل المرتب')
            //             ->body('تم حساب المرتب وإنشاء السجل بنجاح.')
            //             ->success()
            //             ->send();
            //     })
            //     ->requiresConfirmation(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
    }
}
