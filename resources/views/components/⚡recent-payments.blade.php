<?php

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public ?array $payments = null;

    public function mount(): void
    {
        $this->loadPayments();
    }

    public function loadPayments(): void
    {
        $this->payments = Cache::remember('recent_payments', now()->addMinutes(15), function () {
            return Payment::with(['invoice.client', 'contact'])
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date->format('M j'),
                    'client_name' => $payment->invoice?->client?->abbreviation ?? $payment->invoice?->client?->name ?? 'Unknown',
                    'invoice_number' => $payment->invoice?->invoice_number,
                    'method' => $payment->payment_method->value,
                    'status' => $payment->status->value,
                    'status_color' => $payment->status === PaymentStatus::COMPLETED ? 'green' : ($payment->status === PaymentStatus::PENDING ? 'yellow' : 'red'),
                ])
                ->toArray();
        });
    }

    #[On('payment-created')]
    public function clearCache(): void
    {
        Cache::forget('recent_payments');
        $this->loadPayments();
    }

    public function refresh(): void
    {
        Cache::forget('recent_payments');
        $this->loadPayments();
    }
};
?>

<div class="flex h-full flex-col p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Payments</h3>
        <button wire:click="refresh" wire:loading.attr="disabled" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <flux:icon.arrow-path class="size-4" wire:loading.class="animate-spin" />
        </button>
    </div>

    @if(empty($payments))
        <div class="flex flex-1 items-center justify-center">
            <p class="text-sm text-gray-500">No payments yet</p>
        </div>
    @else
        <div class="flex-1 overflow-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/50">
                            <td class="py-2 pr-2">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $payment['client_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $payment['invoice_number'] }}</div>
                            </td>
                            <td class="py-2 pr-2 text-right">
                                <div class="font-medium text-gray-900 dark:text-white">${{ number_format($payment['amount'], 2) }}</div>
                                <div class="text-xs text-gray-500">{{ $payment['payment_date'] }}</div>
                            </td>
                            <td class="py-2 text-right">
                                <flux:badge size="sm" :color="$payment['status_color']">{{ ucfirst($payment['method']) }}</flux:badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Cached for 15 min</p>
</div>