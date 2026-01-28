<?php

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[On('product-created')]
    #[On('product-updated')]
    public function refresh(): void {}

    #[Computed]
    public function products()
    {
        return Product::orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }
};
?>

<!--suppress RequiredAttributes -->
<div>
    <flux:table :paginate="$this->products">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'sku'" :direction="$sortDirection"
                               wire:click="sort('sku')">
                SKU
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">
                Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'description'" :direction="$sortDirection"
                               wire:click="sort('description')">
                Description
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'price'" :direction="$sortDirection"
                               wire:click="sort('price')">
                Price
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'active'" :direction="$sortDirection"
                               wire:click="sort('active')">
                Status
            </flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($this->products as $product)
                <flux:table.row :key="$product->id">
                    <flux:table.cell>{{ $product->sku }}</flux:table.cell>
                    <flux:table.cell>{{ $product->name }}</flux:table.cell>
                    <flux:table.cell class="max-w-xs truncate">{{ $product->description }}</flux:table.cell>
                    <flux:table.cell>{{ formatMoney($product->price) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$product->active ? 'green' : 'zinc'">
                            {{ $product->active ? 'Active' : 'Inactive' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="start">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                         inset="top bottom"></flux:button>

                            <flux:navmenu>
                                <flux:menu.group heading="{{ $product->sku }}">
                                    <flux:menu.separator></flux:menu.separator>
                                    <flux:menu.item wire:click="$dispatch('edit-product', { productId: {{ $product->id }} })" icon="pencil">Edit</flux:menu.item>
                                </flux:menu.group>
                            </flux:navmenu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
