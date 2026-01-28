<x-layouts::app :title="__('Contacts')">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex justify-end">
            <livewire:create-contact />
        </div>
        <livewire:contact-list />
        <livewire:edit-contact />
    </div>
</x-layouts::app>
