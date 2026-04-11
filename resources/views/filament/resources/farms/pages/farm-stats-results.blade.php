<x-filament-panels::page>
    @if($report)
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-filament::card>
                    <div class="flex flex-col items-center justify-center p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي التكاليف</span>
                        <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            {{ number_format($report->total_expenses) }} EGP
                        </span>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="flex flex-col items-center justify-center p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">إجمالي الإيرادات</span>
                        <span class="text-2xl font-bold text-success-600 dark:text-success-400">
                            {{ number_format($report->total_revenue) }} EGP
                        </span>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="flex flex-col items-center justify-center p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">صافي الربح / الخسارة</span>
                        <span
                            class="text-2xl font-bold {{ $report->net_profit >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ number_format($report->net_profit) }} EGP
                        </span>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="flex flex-col items-center justify-center p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">هامش الربح</span>
                        <span class="text-2xl font-bold text-info-600">
                            {{ number_format($report->profit_margin, 2) }}%
                        </span>
                    </div>
                </x-filament::card>
            </div>

            <x-filament::section>
                <x-slot name="heading">
                    تفاصيل التقرير
                </x-slot>

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="font-medium">تاريخ التقرير:</span>
                        <span>{{ $report->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="font-medium">عدد الدفعات:</span>
                        <span>{{ $report->batch_count }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="font-medium">إجمالي تكاليف الدفعات:</span>
                        <span>{{ number_format($report->total_expenses - $report->extra_expenses) }} EGP</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="font-medium">إجمالي إيرادات الدفعات:</span>
                        <span>{{ number_format($report->total_revenue - $report->extra_revenue) }} EGP</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="font-medium">تكاليف إضافية (مزرعة):</span>
                        <span class="text-danger-600 font-bold">+ {{ number_format($report->extra_expenses) }} EGP</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="font-medium">إيرادات إضافية (مزرعة):</span>
                        <span class="text-success-600 font-bold">+ {{ number_format($report->extra_revenue) }} EGP</span>
                    </div>
                    <div class="py-2">
                        <span class="font-medium">الفلاتر المستخدمة:</span>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if($report->filters['annual_basis'] ?? false)
                                <x-filament::badge color="info">أساس سنوي
                                    ({{ $report->filters['year'] ?? now()->year }})</x-filament::badge>
                            @endif
                            @if($report->filters['start_date'] ?? null)
                                <x-filament::badge color="info">من: {{ $report->filters['start_date'] }}</x-filament::badge>
                            @endif
                            @if($report->filters['end_date'] ?? null)
                                <x-filament::badge color="info">إلى: {{ $report->filters['end_date'] }}</x-filament::badge>
                            @endif
                            @if(!empty($report->filters['batch_ids']))
                                <x-filament::badge color="info">دفعات محددة</x-filament::badge>
                            @endif
                        </div>
                    </div>
                </div>
            </x-filament::section>

            @if($report->other_transactions && count($report->other_transactions) > 0)
                <x-filament::section collapsible>
                    <x-slot name="heading">
                        تفاصيل معاملات المزرعة الأخرى
                    </x-slot>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 border">التاريخ</th>
                                    <th class="px-4 py-2 border">البيان</th>
                                    <th class="px-4 py-2 border">الفئة</th>
                                    <th class="px-4 py-2 border">الدفعة</th>
                                    <th class="px-4 py-2 border">النوع</th>
                                    <th class="px-4 py-2 border">المبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report->other_transactions as $tx)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-2 border">{{ $tx['date'] }}</td>
                                        <td class="px-4 py-2 border">{{ $tx['description'] }}</td>
                                        <td class="px-4 py-2 border">{{ $tx['category'] }}</td>
                                        <td class="px-4 py-2 border text-gray-500">{{ $tx['batch_code'] }}</td>
                                        <td class="px-4 py-2 border">
                                            <x-filament::badge :color="$tx['type'] === 'revenue' ? 'success' : 'danger'">
                                                {{ $tx['type_label'] }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-2 border font-bold">
                                            {{ number_format($tx['amount']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endif

            @if($report->status === 'processing')
                <x-filament::section>
                    <div class="flex items-center justify-center p-8 space-x-4 space-x-reverse">
                        <x-filament::loading-indicator class="h-8 w-8 text-primary-600" />
                        <span class="text-lg font-medium text-gray-600 dark:text-gray-400">جاري معالجة البيانات وتحديث
                            الإحصائيات...</span>
                    </div>
                </x-filament::section>
            @endif

            @if($report->status === 'failed')
                <x-filament::section>
                    <div class="p-4 bg-danger-50 dark:bg-danger-900/50 text-danger-600 dark:text-danger-400 rounded-lg">
                        <h3 class="text-lg font-bold">فشل إنشاء التقرير</h3>
                        <p class="mt-1">{{ $report->error_message }}</p>
                    </div>
                </x-filament::section>
            @endif
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-12 space-y-4 text-center">
            <x-heroicon-o-document-chart-bar class="h-16 w-16 text-gray-400" />
            <h3 class="text-xl font-bold text-gray-600 dark:text-gray-400">لا يوجد تقرير متاح</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-md">يرجى استخدام إجراء "إحصائيات المزرعة" من قائمة العمليات
                لإنشاء تقرير جديد لهذه المزرعة.</p>
            <x-filament::button :href="route('filament.admin.resources.farms.index')" color="gray"
                icon="heroicon-o-arrow-right">
                العودة لقائمة المزارع
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>