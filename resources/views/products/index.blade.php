<x-layouts::app :title="__('Products')">
    <div class="max-w-7xl mx-auto space-y-4">
        <div class="flex justify-end">
            <livewire:create-product />
        </div>
        <livewire:product-list />
        <livewire:edit-product />
    </div>
</x-layouts::app>