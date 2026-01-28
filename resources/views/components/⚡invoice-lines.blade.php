<?php

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

new class extends Component {
    public Invoice $invoice;

    #[Validate('nullable|exists:products,id')]
    public ?int $product_id = null;

    #[Validate('nullable|string|max:255')]
    public ?string $sku = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $description = null;

    #[Validate('nullable|integer')]
    public ?int $school_year = null;

    #[Validate('required|numeric|min:0.01')]
    public float $quantity = 1;

    #[Validate('required|numeric|min:0')]
    public float $unit_price = 0;

    public ?int $editingLineId = null;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->school_year = $this->defaultSchoolYear();
    }

    private function defaultSchoolYear(): int
    {
        $now = now();
        return $now->month <= 6 ? $now->year : $now->year + 1;
    }

    #[On('invoice-status-changed')]
    public function refreshInvoice(): void
    {
        $this->invoice->refresh();
    }

    #[Computed]
    public function lines()
    {
        return $this->invoice->lines()->with('product')->orderBy('id')->get();
    }

    #[Computed]
    public function products()
    {
        return Product::where('active', true)->orderBy('name')->get();
    }

    public function updatedProductId($value): void
    {
        if (!$value) {
            return;
        }

        $product = Product::find($value);
        if ($product) {
            $this->sku = $product->sku;
            $this->name = $product->name;
            $this->description = $product->description;
            $this->unit_price = $product->price;
        }
    }

    public function addLine(): void
    {
        if ($this->invoice->isLocked()) {
            return;
        }

        $this->validate();

        $this->invoice->lines()->create([
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'school_year' => $this->school_year,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
        ]);

        $this->invoice->refresh();
        $this->resetForm();
        $this->dispatch('lines-updated');
    }

    public function editLine(int $lineId): void
    {
        if ($this->invoice->isLocked()) {
            return;
        }

        $line = $this->invoice->lines()->find($lineId);
        if (!$line) return;

        $this->editingLineId = $lineId;
        $this->product_id = $line->product_id;
        $this->sku = $line->sku;
        $this->name = $line->name;
        $this->description = $line->description;
        $this->school_year = $line->school_year;
        $this->quantity = $line->quantity;
        $this->unit_price = $line->unit_price;
    }

    public function updateLine(): void
    {
        if ($this->invoice->isLocked() || !$this->editingLineId) {
            return;
        }

        $this->validate();

        $line = $this->invoice->lines()->find($this->editingLineId);
        if (!$line) return;

        $line->update([
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'school_year' => $this->school_year,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
        ]);

        $this->invoice->refresh();
        $this->cancelEdit();
        $this->dispatch('lines-updated');
    }

    public function cancelEdit(): void
    {
        $this->editingLineId = null;
        $this->resetForm();
    }

    public function deleteLine(int $lineId): void
    {
        if ($this->invoice->isLocked()) {
            return;
        }

        $line = $this->invoice->lines()->find($lineId);
        $line?->delete();
        $this->invoice->refresh();
        $this->dispatch('lines-updated');
    }

    private function resetForm(): void
    {
        $this->product_id = null;
        $this->sku = null;
        $this->name = '';
        $this->description = null;
        $this->school_year = $this->defaultSchoolYear();
        $this->quantity = 1;
        $this->unit_price = 0;
    }
};
?>

<div>
    <flux:card class="bg-gray-50 mt-8">
        <div class="flex justify-between items-center mb-4">
            <flux:heading size="lg">Invoice Lines</flux:heading>
            <flux:text class="font-semibold">Total: {{ formatMoney($invoice->total) }}</flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Product</flux:table.column>
                <flux:table.column>SKU</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Description</flux:table.column>
                <flux:table.column>School Year</flux:table.column>
                <flux:table.column class="text-right">Qty</flux:table.column>
                <flux:table.column class="text-right">Unit Price</flux:table.column>
                <flux:table.column class="text-right">Amount</flux:table.column>
                @unless($invoice->isLocked())
                    <flux:table.column class="w-24"></flux:table.column>
                @endunless
            </flux:table.columns>
            <flux:table.rows>
                @forelse($this->lines as $line)
                    <flux:table.row>
                        <flux:table.cell>{{ $line->product?->name }}</flux:table.cell>
                        <flux:table.cell>{{ $line->sku }}</flux:table.cell>
                        <flux:table.cell>{{ $line->name }}</flux:table.cell>
                        <flux:table.cell class="text-gray-500">{{ $line->description }}</flux:table.cell>
                        <flux:table.cell>{{ $line->school_year_formatted }}</flux:table.cell>
                        <flux:table.cell class="text-right">{{ $line->quantity }}</flux:table.cell>
                        <flux:table.cell class="text-right">${{ number_format($line->unit_price, 2) }}</flux:table.cell>
                        <flux:table.cell class="text-right">${{ number_format($line->amount, 2) }}</flux:table.cell>
                        @unless($invoice->isLocked())
                            <flux:table.cell>
                                <div class="flex gap-2 justify-end">
                                    <flux:button size="xs" variant="ghost" wire:click="editLine({{ $line->id }})">Edit</flux:button>
                                    <flux:button size="xs" variant="ghost" color="red" wire:click="deleteLine({{ $line->id }})" wire:confirm="Delete this line?">Delete</flux:button>
                                </div>
                            </flux:table.cell>
                        @endunless
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="{{ $invoice->isLocked() ? 8 : 9 }}" class="text-center text-gray-500">
                            No lines yet.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @unless($invoice->isLocked())
            <form wire:submit="{{ $editingLineId ? 'updateLine' : 'addLine' }}" class="mt-6 border-t pt-6">
                <flux:heading size="md" class="mb-4">{{ $editingLineId ? 'Edit Line' : 'Add Line' }}</flux:heading>
                <div class="grid grid-cols-4 gap-4">
                    <flux:select wire:model.live="product_id" label="Product" placeholder="Select product...">
                        <flux:select.option :value="null">-- None --</flux:select.option>
                        @foreach($this->products as $product)
                            <flux:select.option :value="$product->id">{{ $product->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="sku" label="SKU" placeholder="SKU..." />
                    <flux:input wire:model="name" label="Name" placeholder="Line item name..." />
                    <flux:input wire:model="description" label="Description" placeholder="Optional description..." />
                </div>
                <div class="grid grid-cols-4 gap-4 mt-4">
                    <flux:input wire:model="school_year" label="School Year" type="number" placeholder="e.g. 2025" />
                    <flux:input wire:model="quantity" label="Quantity" type="number" step="0.01" min="0.01" />
                    <flux:input wire:model="unit_price" label="Unit Price" type="number" step="0.01" min="0" />
                </div>
                <div class="mt-4 flex gap-2">
                    <flux:button type="submit" variant="primary">
                        {{ $editingLineId ? 'Update Line' : 'Add Line' }}
                    </flux:button>
                    @if($editingLineId)
                        <flux:button type="button" variant="ghost" wire:click="cancelEdit">Cancel</flux:button>
                    @endif
                </div>
            </form>
        @endunless
    </flux:card>
</div>
