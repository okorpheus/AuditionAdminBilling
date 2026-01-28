<x-layouts::app :title="__('Clients')">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex justify-end">
            <livewire:create-client />
        </div>
        <livewire:client-list />
    </div>
</x-layouts::app>
