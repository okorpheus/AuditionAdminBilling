<?php

use App\Models\Payment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    #[Computed]
    public function payments()
    {
        return Payment::orderBy('payment_date', 'desc')->orderBy('created_at', 'desc')->get();
    }

    #[On('payment-created')]
    #[On('payment-updated')]
    public function refresh(): void
    {
    }

    public function deletePayment(Payment $payment): void
    {
        $payment->delete();
    }

};
?>

<div>
    <flux:heading size="xl">Payments</flux:heading>

    <flux:table :pagination="$this->payments">
        <flux:table.columns>
            <flux:table.column>Payment Date</flux:table.column>
            <flux:table.column>Invoice</flux:table.column>
            <flux:table.column>Contact</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Payment Method<br/>Reference</flux:table.column>
            <flux:table.column>Fee Amount</flux:table.column>
            <flux:table.column>Amount Paid</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->payments as $payment)
                <flux:table.row>
                    <flux:table.cell>{{ $payment->payment_date->local()->format('m/d/Y') }}</flux:table.cell>
                    <flux:table.cell>{{ $payment->invoice->invoice_number }}</flux:table.cell>
                    <flux:table.cell>{{ $payment->contact?->full_name }}</flux:table.cell>
                    <flux:table.cell>{{ $payment->status->label() }}</flux:table.cell>
                    <flux:table.cell>{{ $payment->payment_method->label() }}<br>{{ $payment->reference }}</flux:table.cell>
                    <flux:table.cell>{{ formatMoney($payment->fee_amount) }}</flux:table.cell>
                    <flux:table.cell>{{ formatMoney($payment->amount) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                            <flux:navmenu>
                                <flux:menu.item
                                    wire:click="$dispatch('edit-payment', { paymentId: {{ $payment->id }} })"
                                    icon="pencil">Edit
                                </flux:menu.item>
                                <flux:menu.item
                                    wire:click="deletePayment({{ $payment->id }})"
                                    wire:confirm="Are you sure you want to delete this payment?"
                                    icon="trash"
                                    variant="danger">Delete
                                </flux:menu.item>
                            </flux:navmenu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
