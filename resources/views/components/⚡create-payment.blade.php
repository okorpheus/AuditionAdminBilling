<?php

use App\Casts\MoneyCast;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Validation\Rules\Enum;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|integer|exists:invoices,id')]
    public ?int $invoice_id = null;

    #[Validate('nullable|integer|exists:contacts,id')]
    public ?int $contact_id = null;

    #[Validate('required|date')]
    public ?string $payment_date = null;

    #[Validate(['required', new Enum(PaymentStatus::class)])]
    public ?PaymentStatus $status = PaymentStatus::COMPLETED;

    #[Validate(['required', new Enum(PaymentMethod::class)])]
    public ?PaymentMethod $payment_method = PaymentMethod::CHECK;

    #[Validate('nullable|string')]
    public ?string $reference = null;

    #[Validate('required|numeric|min:0.01')]
    public float $amount = 0;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    #[Computed]
    public function invoices()
    {
        return Invoice::where('status', InvoiceStatus::POSTED)->with('client')->get()->sortBy('client.abbreviation');
    }

    #[Computed]
    public function contacts()
    {
        if (!$this->invoice_id) {
            return collect();
        }
        $invoice = Invoice::find($this->invoice_id);
        return $invoice?->client?->contacts ?? collect();
    }

    public function updatedInvoiceId(): void
    {
        $this->contact_id = null; // Reset when invoice changes
    }


    public function mount(?int $invoice_id = null): void
    {
        $this->invoice_id   = $invoice_id;
        $this->payment_date = now()->local()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate();
        Payment::create([
            'invoice_id' => $this->invoice_id,
            'contact_id'=> $this->contact_id,
            'payment_date' => $this->payment_date,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'notes' => $this->notes,
        ]);

        $this->reset();
        Flux::modal('create-payment')->close();
        $this->dispatch('payment-created');
    }


};
?>

<div>
    <flux:modal.trigger name="create-payment">
        <flux:button icon="plus" variant="primary">
            Create Payment
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-payment" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Record Payment</flux:heading>

            <flux:input wire:model="payment_date" label="Payment Date" type="date"/>

            <flux:select wire:model.live="invoice_id" label="Invoice" placeholder="Choose an invoice...">
                <option value="">Select an invoice...</option>
                @foreach ($this->invoices as $invoice)
                    <option value="{{ $invoice->id }}">{{ $invoice->client->abbreviation }}
                        - {{ $invoice->invoice_number }} - Balance: {{ formatMoney($invoice->balance_due) }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="contact_id" label="Contact" placeholder="Choose a contact..."
                         :disabled="!$this->invoice_id">
                <option value="">No contact recorded</option>
                @foreach($this->contacts as $contact)
                    <flux:select.option :value="$contact->id">{{ $contact->full_name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="payment_method" label="Payment Method">
                @foreach(PaymentMethod::cases() as $method)
                    <flux:select.option :value="$method">{{ $method->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="reference" label="Reference"/>

            <flux:input wire:model="amount" label="Amount" type="number" step="0.01"/>

            <flux:textarea wire:model="notes" label="Notes"/>

            <flux:button type="submit" variant="primary">Save Payment</flux:button>
        </form>
    </flux:modal>

</div>
