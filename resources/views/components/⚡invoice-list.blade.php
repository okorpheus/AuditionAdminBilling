<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;


new class extends Component {
    use WithPagination;

    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[On('invoice-created')]
    public function refresh(): void {}

    #[Computed]
    public function invoices()
    {
        return Invoice::orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }

};
?>

    <!--suppress RequiredAttributes -->
<div>
    <flux:table :pagination="$this->invoices">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'invoice_number'" :direction="$sortDirection"
                               wire:click="sort('invoice_number')">
                Invoice Number
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'client_id'" :direction="$sortDirection"
                               wire:click="sort('client_id')">
                Client
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                               wire:click="sort('status')">
                Status
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'invoice_date'" :direction="$sortDirection"
                               wire:click="sort('invoice_date')">
                Invoice Date
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'sent_at'" :direction="$sortDirection"
                               wire:click="sort('sent_at')">
                Sent
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'due_date'" :direction="$sortDirection"
                               wire:click="sort('due_date')">
                Due Date
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'total'" :direction="$sortDirection"
                               wire:click="sort('total')">
                Total
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($this->invoices as $invoice)
                <flux:table.row :key="$invoice->id">
                    <flux:table.cell>{{ $invoice->invoice_number }}</flux:table.cell>
                    <flux:table.cell>{{ $invoice->client->abbreviation }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$invoice->status->color()" rounded size="sm">
                            {{ $invoice->status->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $invoice->invoice_date?->format('m/d/Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if($invoice->sent_at)
                            <flux:badge color="green" rounded
                                        size="sm">{{ $invoice->sent_at->format('m/d/Y') }}</flux:badge>
                        @elseif($invoice->status === InvoiceStatus::POSTED)
                            <flux:badge color="red" rounded size="sm">Not Sent</flux:badge>
                        @endif

                    </flux:table.cell>
                    <flux:table.cell>
                        @if($invoice->due_date)
                            <flux:badge size="sm" rounded
                            :color="$invoice->due_date?->isPast() && $invoice->status === InvoiceStatus::POSTED ? 'red' : 'blue'">
                                {{ $invoice->due_date?->format('m/d/Y') }}
                            </flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ formatMoney($invoice->total) }}</flux:table.cell>

                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
