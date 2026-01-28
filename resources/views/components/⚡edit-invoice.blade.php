<?php

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;


new class extends Component {
    public $invoice;

    #[Validate('required|exists:clients,id')]
    public ?int $client_id = null;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    #[Validate('nullable|string')]
    public ?string $internal_notes = null;

    public function mount($invoice = null): void
    {
        $this->invoice        = $invoice;
        $this->client_id      = $invoice?->client_id;
        $this->notes          = $invoice?->notes;
        $this->internal_notes = $invoice?->internal_notes;
    }

    public function updateClient(): void
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id'
        ]);

        $this->invoice->update(['client_id' => $this->client_id]);
    }

    public function updateNotes(): void
    {
        $this->validate([
            'notes'          => 'nullable|string',
            'internal_notes' => 'nullable|string'
        ]);
        $this->invoice->update([
            'notes'          => $this->notes,
            'internal_notes' => $this->internal_notes,
        ]);
    }

    public function setStatus($newStatus): void
    {
        $updatedValue = match ($newStatus) {
            'posted' => InvoiceStatus::POSTED,
            'draft' => InvoiceStatus::DRAFT,
            'void' => InvoiceStatus::VOID,
            'paid' => InvoiceStatus::PAID,
            default => $this->invoice->status
        };
        $this->invoice->update([
            'status' => $updatedValue,
        ]);

        if ($newStatus === 'posted') {
            $this->invoice->update([
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
            ]);
        }
    }

    #[Computed]
    public function clients()
    {
        return Client::where('status', 'active')->orderBy('abbreviation')->get();
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-8">
        <flux:heading size="xl" class="mb-3">Edit Invoice</flux:heading>
        <div>
            @if($this->invoice->status === InvoiceStatus::DRAFT)
                <flux:button variant="primary" color="red" wire:click="setStatus('void')">Void Invoice</flux:button>
                <flux:button variant="primary" color="green" wire:click="setStatus('posted')">Post Invoice</flux:button>
            @elseif($this->invoice->status === InvoiceStatus::POSTED)
                <flux:button variant="primary" color="red" wire:click="setStatus('void')">Void Invoice</flux:button>
                <flux:button variant="primary" color="amber" wire:click="setStatus('draft')">Un-Post Invoice</flux:button>
            @elseif($this->invoice->status === InvoiceStatus::VOID)
                <flux:button variant="primary" color="blue" wire:click="setStatus('draft')">Restore Invoice</flux:button>
            @endif
        </div>
    </div>
    <flux:card class="bg-gray-50">

        <div class="grid grid-cols-3 gap-4">
            <div>
                <div class="flex gap-3">
                    <flux:heading size="md">ID</flux:heading>
                    <flux:text>{{ $invoice->id }}</flux:text>
                </div>
                <div class="flex gap-3">
                    <flux:heading size="md">Invoice Number</flux:heading>
                    <flux:text>{{ $invoice->invoice_number }}</flux:text>
                </div>
                <div class="flex gap-3">
                    <flux:heading size="md">UUID</flux:heading>
                    <flux:text>{{ $invoice->uuid }}</flux:text>
                </div>
            </div>
            <div>
                @if($invoice->status !== InvoiceStatus::DRAFT)
                    <div class="flex gap-3">
                        <flux:heading size="md">Client</flux:heading>
                        <flux:text>{{ $invoice->client->name }}</flux:text>
                    </div>
                    <div class="flex gap-3">
                        <flux:heading size="md">Invoice Date</flux:heading>
                        <flux:text>{{ $invoice->invoice_date?->format('m/d/Y') }}</flux:text>
                    </div>
                    <div class="flex gap-3">
                        <flux:heading size="md">Due Date</flux:heading>
                        <flux:text>{{ $invoice->due_date?->format('m/d/Y') }}</flux:text>
                    </div>
                    <div class="flex gap-3">
                        <flux:heading size="md">Sent Date</flux:heading>
                        <flux:text>{{ $invoice->sent_at?->format('m/d/Y') }}</flux:text>
                    </div>
                @else
                    <flux:select wire:model="client_id" wire:change="updateClient" label="Client"
                                 placeholder="Choose client..."
                                 :disabled="$invoice->status !== InvoiceStatus::DRAFT">
                        @foreach($this->clients as $client)
                            <flux:select.option :value="$client->id">{{ $client->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            </div>
            <div>
                <div class="flex gap-3">
                    <flux:heading size="md">Status</flux:heading>
                    <flux:badge color="{{ $invoice->status->color() }}" size="sm">
                        {{ $invoice->status->label() }}
                    </flux:badge>
                </div>
                <div class="flex gap-3">
                    <flux:heading size="md">Created</flux:heading>
                    <flux:text>{{ $invoice->created_at->local()->format('m/d/Y | h:m:s') }}</flux:text>
                </div>
                <div class="flex gap-3">
                    <flux:heading size="md">Last Updated</flux:heading>
                    <flux:text>{{ $invoice->updated_at->local()->format('m/d/Y | h:m:s') }}</flux:text>
                </div>
            </div>

        </div>
    </flux:card>

    <form wire:submit="updateNotes" x-data="{ dirty: false }" x-on:submit="dirty = false">
        <flux:card class="bg-gray-50 mt-8">
            <div class="grid grid-cols-2 gap-4">

                <flux:textarea wire:model="notes" label="Notes" placeholder="Add notes..." rows="5"
                               x-on:input="dirty = true"/>
                <flux:textarea wire:model="internal_notes" label="Internal Notes" placeholder="Add internal notes..."
                               rows="5" x-on:input="dirty = true"/>

            </div>
            <div class="text-right" x-show="dirty" x-cloak>
                <flux:button type="submit" variant="primary" class="mt-4">Save Notes</flux:button>
            </div>
        </flux:card>
    </form>
</div>
