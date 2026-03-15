<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::callout icon="heroicon-o-list-bullet" color="gray">
            <x-slot name="heading">
                سجل كشوفات الحساب - {{ $record->name }}
            </x-slot>
            <x-slot name="description">
                هنا يمكنك مراجعة كافة الدورات المالية السابقة والحالية لهذا التاجر.
            </x-slot>
        </x-filament::callout>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
