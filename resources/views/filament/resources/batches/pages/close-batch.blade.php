<x-filament-panels::page>
    <form wire:submit="closeBatch">
        {{ $this->form }}

        <div class="fi-form-actions mt-6 flex gap-3">
            <x-filament::button type="submit" color="danger">
                تأكيد إقفال الدورة
            </x-filament::button>
            <x-filament::button color="gray" tag="a" :href="$this->getResource()::getUrl('view', ['record' => $record])">
                إلغاء
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>