<x-layouts::app :title="__('Clients')">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex justify-end">
            <livewire:create-client />
        </div>
        <livewire:client-list />
        <livewire:edit-client />
        <livewire:add-client-contact />
        <livewire:remove-client-contact />
        <livewire:set-primary-contact />
    </div>
</x-layouts::app>
