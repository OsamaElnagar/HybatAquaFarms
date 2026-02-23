<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @php
            $reports = [
                [
                    'type' => 'daily_pdf',
                    'title' => 'تقرير اليوم بأكمله (PDF)',
                    'description' => 'ملخص شامل لكل العمليات في المزرعة.',
                    'icon' => 'heroicon-o-document-text',
                    'color' => 'primary',
                ],
                [
                    'type' => 'sales',
                    'title' => 'المبيعات',
                    'description' => 'ملخص مبيعات هذا الشهر.',
                    'icon' => 'heroicon-o-banknotes',
                    'color' => 'success',
                ],
                [
                    'type' => 'harvest',
                    'title' => 'الحصاد',
                    'description' => 'إجمالي عمليات الحصاد لهذا الشهر.',
                    'icon' => 'heroicon-o-scale',
                    'color' => 'warning',
                ],
                [
                    'type' => 'feedStock',
                    'title' => 'تنبيهات مخزون الأعلاف',
                    'description' => 'نواقص الأعلاف بالمستودعات.',
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'color' => 'danger',
                ],
                [
                    'type' => 'batches',
                    'title' => 'الدورات النشطة',
                    'description' => 'بيانات الدورات ومعدل التحويل.',
                    'icon' => 'heroicon-o-arrow-path-rounded-square',
                    'color' => 'info',
                ],
                [
                    'type' => 'expenses',
                    'title' => 'المصروفات',
                    'description' => 'منصرفات السندات لهذا الشهر.',
                    'icon' => 'heroicon-o-currency-dollar',
                    'color' => 'danger',
                ],
                [
                    'type' => 'cashflow',
                    'title' => 'الخزينة والقيود',
                    'description' => 'حركة الأموال والقيود اليومية.',
                    'icon' => 'heroicon-o-arrows-right-left',
                    'color' => 'success',
                ],
                [
                    'type' => 'advances',
                    'title' => 'السلف',
                    'description' => 'أرصدة سلف الموظفين المتبقية.',
                    'icon' => 'heroicon-o-users',
                    'color' => 'primary',
                ],
            ];
        @endphp

        @foreach($reports as $report)
            <x-filament::section class="flex flex-col h-full justify-between">
                <div>
                    <h3 class="text-lg font-bold">{{ $report['title'] }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $report['description'] }}</p>
                </div>

                <div class="mt-4 flex justify-end">
                    <x-filament::button wire:click="sendReport('{{ $report['type'] }}')" color="{{ $report['color'] }}"
                        icon="{{ $report['icon'] }}" wire:loading.attr="disabled"
                        wire:target="sendReport('{{ $report['type'] }}')">
                        <span wire:loading.remove wire:target="sendReport('{{ $report['type'] }}')">
                            إرسال لـ Telegram
                        </span>
                        <span wire:loading wire:target="sendReport('{{ $report['type'] }}')">
                            جاري الإرسال...
                        </span>
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>