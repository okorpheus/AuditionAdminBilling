<?php

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Flux\Flux;

new class extends Component {
    public ?int $productId = null;

    #[Validate('required|string|max:50')]
    public string $sku = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('boolean')]
    public bool $active = true;

    #[On('edit-product')]
    public function edit(int $productId): void
    {
        $this->productId = $productId;
        $product = Product::findOrFail($productId);

        $this->sku = $product->sku;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->price = (string) $product->getRawOriginal('price') / 100;
        $this->active = $product->active;

        $this->resetValidation();
        Flux::modal('edit-product')->show();
    }

    public function save(): void
    {
        $this->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $this->productId,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'active' => 'boolean',
        ]);

        $product = Product::findOrFail($this->productId);
        $product->update([
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'price' => $this->price,
            'active' => $this->active,
        ]);

        $this->reset();
        Flux::modal('edit-product')->close();
        $this->dispatch('product-updated');
    }
};
?>

<div>
    <flux:modal name="edit-product" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Edit Product</flux:heading>

            <flux:input label="SKU" wire:model="sku" />
            <flux:input label="Name" wire:model="name" />
            <flux:textarea label="Description" wire:model="description" rows="3" />
            <flux:input label="Price" wire:model="price" type="number" step="0.01" min="0" />
            <flux:switch label="Active" wire:model="active" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>
</div>