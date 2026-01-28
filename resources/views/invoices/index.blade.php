<x-layouts::app :title="__('Contacts')">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex justify-end">
            <livewire:create-invoice />
        </div>
        <livewire:invoice-list />
    </div>
</x-layouts::app>
