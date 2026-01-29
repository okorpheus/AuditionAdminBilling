<?php

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Flux\Flux;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    public ?Payment $payment = null;

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

    public $contacts = [];

    #[Computed]
    public function invoices()
    {
        return Invoice::where('status', InvoiceStatus::POSTED)->with('client')->get()->sortBy('client.abbreviation');
    }

    public function loadContacts(): void
    {
        if (!$this->invoice_id) {
            $this->contacts = [];
            return;
        }
        $invoice = Invoice::find($this->invoice_id);
        $this->contacts = $invoice?->client?->contacts ?? collect();
    }

    public function updatedInvoiceId(): void
    {
        $this->loadContacts();
        $this->contact_id = null;
    }

    #[On('edit-payment')]
    public function loadPayment(int $paymentId): void
    {
        $this->payment = Payment::findOrFail($paymentId);

        $this->invoice_id = $this->payment->invoice_id;
        $this->loadContacts();
        $this->contact_id = $this->payment->contact_id;
        $this->payment_date = $this->payment->payment_date->format('Y-m-d');
        $this->status = $this->payment->status;
        $this->payment_method = $this->payment->payment_method;
        $this->reference = $this->payment->reference;
        $this->amount = $this->payment->amount;
        $this->notes = $this->payment->notes;

        Flux::modal('edit-payment')->show();
    }

    public function save(): void
    {
        $this->validate();

        $this->payment->update([
            'invoice_id' => $this->invoice_id,
            'contact_id' => $this->contact_id,
            'payment_date' => $this->payment_date,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'notes' => $this->notes,
        ]);

        Flux::modal('edit-payment')->close();
        $this->dispatch('payment-updated');
    }
};
?>

<div>
    <flux:modal name="edit-payment" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Edit Payment</flux:heading>

            <flux:input wire:model="payment_date" label="Payment Date" type="date"/>

            <flux:select wire:model.live="invoice_id" label="Invoice" placeholder="Choose an invoice...">
                <option value="">Select an invoice...</option>
                @foreach ($this->invoices as $invoice)
                    <option value="{{ $invoice->id }}">{{ $invoice->client->abbreviation }}
                        - {{ $invoice->invoice_number }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="contact_id" label="Contact" placeholder="Choose a contact...">
                <option value="">No contact recorded</option>
                @foreach($contacts as $contact)
                    <flux:select.option :value="$contact->id">{{ $contact->full_name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model="status" label="Status">
                @foreach(PaymentStatus::cases() as $s)
                    <flux:select.option :value="$s">{{ $s->label() }}</flux:select.option>
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

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Update Payment</flux:button>
                <flux:button type="button" variant="ghost" wire:click="$dispatch('close-modal', 'edit-payment')">Cancel</flux:button>
            </div>
        </form>
    </flux:modal>
</div>