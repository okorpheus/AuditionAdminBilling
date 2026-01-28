<?php

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    #[Validate('required|integer|exists:clients,id')]
    public int $client_id;

    public InvoiceStatus $status = InvoiceStatus::DRAFT;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    #[Validate('nullable|string')]
    public ?string $internal_notes = null;

    public function save(): void
    {
        $this->validate();

        Invoice::create([
            'client_id'      => $this->client_id,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'internal_notes' => $this->notes,
        ]);

        $this->reset();
        Flux::modal('create-invoice')->close();
        $this->dispatch('invoice-created');
    }

    #[Computed]
    public function clients()
    {
        return Client::where('status', 'active')->orderBy('abbreviation')->get();
    }
};
?>

<div>
    <flux:modal.trigger name="create-invoice">
        <flux:button icon="plus" variant="primary">
            Create Invoice
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-invoice" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Invoice</flux:heading>

            <flux:select wire:model="client_id" label="Client" placeholder="Choose client...">
                @foreach($this->clients as $client)
                    <flux:select.option :value="$client->id">{{ $client->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="notes" label="Notes" placeholder="Add notes to this invoice..."></flux:textarea>
            <flux:textarea wire:model="internal_notes" label="Internal Notes" placeholder="Add internal notes to this invoice..."></flux:textarea>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create</flux:button>
            </div>
        </form>



    </flux:modal>
</div>
