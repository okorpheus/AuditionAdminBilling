<x-layouts::app :title="__('Contacts')">
    <div class="max-w-7xl mx-auto space-y-4">
        <livewire:edit-invoice :invoice="$invoice" />

        <livewire:invoice-lines :invoice="$invoice" />
    </div>
</x-layouts::app>
