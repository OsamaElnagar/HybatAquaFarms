<div>
    @php
        $lines = $getState() ?? [];
        $totalDebit = collect($lines)->sum('debit');
        $totalCredit = collect($lines)->sum('credit');
        $difference = $totalDebit - $totalCredit;
        $isBalanced = abs($difference) < 0.01;
    @endphp

    <div class="rounded-lg border border-gray-300 dark:border-gray-600 p-4 bg-gray-50 dark:bg-gray-800">
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-gray-600 dark:text-gray-400">إجمالي المدين</div>
                <div class="text-lg font-bold text-success-600">
                    {{ number_format($totalDebit, 2) }} EGP
                </div>
            </div>

            <div>
                <div class="text-gray-600 dark:text-gray-400">إجمالي الدائن</div>
                <div class="text-lg font-bold text-danger-600">
                    {{ number_format($totalCredit, 2) }} EGP
                </div>
            </div>

            <div>
                <div class="text-gray-600 dark:text-gray-400">الفرق</div>
                <div class="text-lg font-bold {{ $isBalanced ? 'text-success-600' : 'text-warning-600' }}">
                    {{ number_format(abs($difference), 2) }} EGP
                    @if($isBalanced)
                        <span class="text-xs">✓ متوازن</span>
                    @else
                        <span class="text-xs">⚠ غير متوازن</span>
                    @endif
                </div>
            </div>
        </div>

        @if(!$isBalanced && ($totalDebit > 0 || $totalCredit > 0))
            <div class="mt-3 text-sm text-warning-600 dark:text-warning-400">
                ⚠ يجب أن يتساوى إجمالي المدين مع إجمالي الدائن
            </div>
        @endif
    </div>
</div>