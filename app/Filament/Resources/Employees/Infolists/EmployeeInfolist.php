<?php

namespace App\Filament\Resources\Employees\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات أساسية')
                    ->schema([
                        TextEntry::make('employee_number')
                            ->label('رقم الموظف')
                            ->columnSpan(1),
                        TextEntry::make('name')
                            ->label('الاسم')
                            ->columnSpan(1),
                        TextEntry::make('phone')
                            ->label('الهاتف')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('phone2')
                            ->label('هاتف بديل')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('national_id')
                            ->label('الرقم القومي')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('farm.name')
                            ->label('المزرعة')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('بيانات التوظيف والراتب')
                    ->schema([
                        TextEntry::make('hire_date')
                            ->label('تاريخ التوظيف')
                            ->date('Y-m-d')
                            ->placeholder('غير محدد')
                            ->columnSpan(1),
                        TextEntry::make('termination_date')
                            ->label('تاريخ إنهاء الخدمة')
                            ->date('Y-m-d')
                            ->placeholder('لا يزال يعمل')
                            ->columnSpan(1),
                        TextEntry::make('basic_salary')
                            ->label('المرتب الشهري')
                            ->formatStateUsing(fn ($state) => number_format($state).' ج.م')
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                                'terminated' => 'منهي',
                                default => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'active' => 'success',
                                'inactive' => 'warning',
                                'terminated' => 'danger',
                                default => 'gray',
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('الإحصائيات المالية')
                    ->schema([
                        TextEntry::make('advances_count')
                            ->label('عدد السلف')
                            ->state(fn ($record) => $record->advances_count)
                            ->badge()
                            ->color('primary')
                            ->columnSpan(1),
                        TextEntry::make('outstanding_advances')
                            ->label('السلف المستحقة')
                            ->state(fn ($record) => number_format($record->total_outstanding_advances).' ج.م')
                            ->badge()
                            ->color(fn ($record) => $record->total_outstanding_advances > 0 ? 'warning' : 'success')
                            ->columnSpan(1),
                        TextEntry::make('salary_records_count')
                            ->label('سجلات الرواتب')
                            ->state(fn ($record) => $record->salary_records_count)
                            ->badge()
                            ->color('info')
                            ->columnSpan(1),
                        TextEntry::make('total_salaries_paid')
                            ->label('إجمالي الرواتب المدفوعة')
                            ->state(fn ($record) => number_format($record->total_salaries_paid).' ج.م')
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('managed_farms_count')
                            ->label('المزارع المُدارة')
                            ->state(fn ($record) => $record->managedFarms()->count())
                            ->badge()
                            ->color('warning')
                            ->columnSpan(1),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإضافة')
                            ->dateTime('Y-m-d H:i')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('معلومات إضافية')
                    ->schema([
                        TextEntry::make('address')
                            ->label('العنوان')
                            ->placeholder('غير محدد')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(fn ($record) => empty($record->address) && empty($record->notes)),
            ]);
    }
}
