<?php

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    #[Validate('required|string|max:50|unique:products,sku')]
    public string $sku = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('boolean')]
    public bool $active = true;

    public function save(): void
    {
        $this->validate();

        Product::create([
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'price' => $this->price,
            'active' => $this->active,
        ]);

        $this->reset();
        Flux::modal('create-product')->close();
        $this->dispatch('product-created');
    }
};
?>

<div>
    <flux:modal.trigger name="create-product">
        <flux:button icon="plus" variant="primary">
            New Product
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-product" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Product</flux:heading>

            <flux:input label="SKU" wire:model="sku" />
            <flux:input label="Name" wire:model="name" />
            <flux:textarea label="Description" wire:model="description" rows="3" />
            <flux:input label="Price" wire:model="price" type="number" step="0.01" min="0" />
            <flux:switch label="Active" wire:model="active" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>