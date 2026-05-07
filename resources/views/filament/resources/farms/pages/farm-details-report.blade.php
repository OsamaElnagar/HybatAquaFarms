<x-filament-panels::page>
    @php $stats = $this->getSummaryStats(); @endphp

    {{-- Date Filters --}}
    <div class="fi-ta-ctn flex flex-wrap items-center gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
        <div class="flex items-center gap-2">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="date"
                    wire:model.live="dateFrom"
                    placeholder="من تاريخ"
                />
            </x-filament::input.wrapper>
            <span class="text-sm text-gray-500">-</span>
            <x-filament::input.wrapper>
                <x-filament::input
                    type="date"
                    wire:model.live="dateTo"
                    placeholder="إلى تاريخ"
                />
            </x-filament::input.wrapper>
            @if($dateFrom || $dateTo)
                <x-filament::button
                    wire:click="$set('dateFrom', null); $set('dateTo', null)"
                    color="gray"
                    size="sm"
                >
                    مسح الفلتر
                </x-filament::button>
            @endif
        </div>
    </div>

    {{-- Grand Totals --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-filament::section>
            <x-slot name="heading">إجمالي المصروفات</x-slot>
            <p class="text-3xl font-bold text-danger-600">{{ number_format($stats['total_expenses'], 2) }} EGP</p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">إجمالي الإيرادات</x-slot>
            <p class="text-3xl font-bold text-success-600">{{ number_format($stats['total_revenue'], 2) }} EGP</p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">صافي الربح / الخسارة</x-slot>
            <p class="text-3xl font-bold {{ $stats['net_profit'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                {{ number_format($stats['net_profit'], 2) }} EGP
            </p>
        </x-filament::section>
    </div>

    {{-- Category Breakdown --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        {{-- Petty Cash --}}
        <x-filament::section>
            <x-slot name="heading">عهد</x-slot>
            <p class="text-lg font-bold text-danger-600">{{ number_format($stats['petty_cash_in'], 2) }} EGP</p>
        </x-filament::section>

        {{-- Farm Expenses --}}
        <x-filament::section>
            <x-slot name="heading">مصروفات وإيرادات المزرعة</x-slot>
            <div class="space-y-2">
                <div>
                    <span class="text-sm text-gray-500">المصروفات</span>
                    <p class="text-lg font-bold text-danger-600">{{ number_format($stats['farm_expenses'], 2) }} EGP</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">الإيرادات</span>
                    <p class="text-lg font-bold text-success-600">{{ number_format($stats['farm_revenue'], 2) }} EGP</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Sales --}}
        <x-filament::section>
            <x-slot name="heading">المبيعات</x-slot>
            <p class="text-lg font-bold text-success-600">{{ number_format($stats['total_sales'], 2) }} EGP</p>
        </x-filament::section>

        {{-- Batches Cost --}}
        <x-filament::section>
            <x-slot name="heading">تكاليف الدفعات</x-slot>
            <div class="space-y-2">
                <div>
                    <span class="text-sm text-gray-500">عدد الدفعات</span>
                    <p class="text-lg font-bold text-primary-600">{{ $stats['batch_count'] }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">تكلفة الزريعة</span>
                    <p class="text-lg font-bold text-danger-600">{{ number_format($stats['batch_hatchery_cost'], 2) }} EGP</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Feed Consumed --}}
        <x-filament::section>
            <x-slot name="heading">العلف المستهلك</x-slot>
            <div class="space-y-2">
                <div>
                    <span class="text-sm text-gray-500">الكمية المستهلكة</span>
                    <p class="text-lg font-bold">{{ number_format($stats['total_feed_quantity'], 3) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">التكلفة التقديرية</span>
                    <p class="text-lg font-bold text-warning-600">{{ number_format($stats['total_feed_cost'], 2) }} EGP</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
