<x-filament-panels::page>
    <form wire:submit="create">
        
            {{ $this->form }}
            <div class="mt-6 flex justify-start rtl:justify-end gap-x-3">
               <x-filament::button type="submit">
                    حفظ
                </x-filament::button>
            </div>
        </form>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            💡 كيفية إدخال الأرصدة الافتتاحية
        </x-slot>

        <div class="prose dark:prose-invert max-w-none">
            <p class="text-sm">
                <strong>الخطوات:</strong>
            </p>
            <ol class="text-sm space-y-2">
                <li>حدد تاريخ بدء التشغيل (تاريخ الأرصدة الافتتاحية)</li>
                <li>أضف بنود القيد لكل حساب برصيده الحالي:
                    <ul class="mt-2">
                        <li><strong>الأصول</strong> (النقدية، المخزون، الذمم المدينة) → <span class="text-success-600">مدين</span></li>
                        <li><strong>الخصوم</strong> (الذمم الدائنة، القروض) → <span class="text-danger-600">دائن</span></li>
                    </ul>
                </li>
                <li>أضف بند الموازنة في حساب "أرصدة افتتاحية" (3900)</li>
                <li>تأكد من تساوي إجمالي المدين مع إجمالي الدائن</li>
            </ol>

            <div class="mt-4 p-4 rounded-lg">
                <p class="text-sm font-semibold mb-2">مثال:</p>
                <table class="text-sm w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-1">الحساب</th>
                            <th class="text-right py-1">مدين</th>
                            <th class="text-right py-1">دائن</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr>
                            <td class="py-1">النقدية بالصندوق (1110)</td>
                            <td class="text-right">50,000</td>
                            <td class="text-right">-</td>
                        </tr>
                        <tr>
                            <td class="py-1">الذمم المدينة (1140)</td>
                            <td class="text-right">20,000</td>
                            <td class="text-right">-</td>
                        </tr>
                        <tr>
                            <td class="py-1">الذمم الدائنة (2110)</td>
                            <td class="text-right">-</td>
                            <td class="text-right">15,000</td>
                        </tr>
                        <tr class="font-bold">
                            <td class="py-1">أرصدة افتتاحية (3900)</td>
                            <td class="text-right">-</td>
                            <td class="text-right text-primary-600">55,000</td>
                        </tr>
                        <tr class="border-t-2 font-bold">
                            <td class="py-1">الإجمالي</td>
                            <td class="text-right">70,000</td>
                            <td class="text-right">70,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>