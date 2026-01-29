<x-layouts::app :title="__('Clients')">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex justify-end">
            <livewire:create-payment />
        </div>
        <livewire:payment-list />
        <livewire:edit-payment />
    </div>
</x-layouts::app>
