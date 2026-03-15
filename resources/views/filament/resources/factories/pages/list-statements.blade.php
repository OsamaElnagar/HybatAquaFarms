<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::callout icon="heroicon-o-list-bullet" color="gray">
            <x-slot name="heading">
                سجل كشوفات الحساب - {{ $record->name }}
            </x-slot>
            <div class="text-sm">
                قائمة بجميع جلسات وكشوفات الحساب السابقة والحالية المصنفة حسب التاريخ.
            </div>
        </x-filament::callout>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
