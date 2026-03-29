<x-filament-panels::page>
    <div class="space-y-4">

        {{-- Session Banner --}}
        @php
            $stmt = $this->activeStatementId 
                ? \App\Models\EmployeeStatement::find($this->activeStatementId)
                : null;
        @endphp

        @if($stmt)
            <x-filament::callout :icon="$stmt->status->getIcon()" :color="$stmt->status->getColor()">
                <x-slot name="heading">
                    {{ $stmt->title ?: 'كشف حساب' }} - {{ $record->name }} 
                    ({{ $stmt->status->getLabel() }})
                </x-slot>
                <div class="text-sm space-y-1">
                    <p>
                        <strong>تاريخ الفتح:</strong> {{ $stmt->opened_at->format('Y-m-d') }}
                        @if($stmt->closed_at)
                            &nbsp;|&nbsp; <strong>تاريخ الإغلاق:</strong> {{ $stmt->closed_at->format('Y-m-d') }}
                        @endif
                    </p>
                    <p>
                        <strong>رصيد افتتاحي:</strong> {{ number_format($stmt->opening_balance) }} EGP
                        &nbsp;|&nbsp;
                        <strong>رصيد الجلسة:</strong> {{ number_format($stmt->opening_balance + $stmt->total_debits - $stmt->total_credits) }} EGP
                    </p>
                    @if($stmt->notes)
                        <p><strong>ملاحظات:</strong> {{ $stmt->notes }}</p>
                    @endif
                </div>
            </x-filament::callout>
        @else
            <x-filament::callout icon="heroicon-o-clock" color="gray">
                <x-slot name="heading">
                    كشف حساب - {{ $record->name }} (السجل الكامل)
                </x-slot>
                <x-slot name="description">
                    إجمالي السلف القائمة: {{ number_format($record->total_outstanding_advances) }} EGP
                </x-slot>
            </x-filament::callout>
        @endif

    </div>
    {{ $this->table }}
</x-filament-panels::page>
