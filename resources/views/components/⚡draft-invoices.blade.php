<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public ?array $invoices = null;

    public function mount(): void
    {
        $this->loadInvoices();
    }

    public function loadInvoices(): void
    {
        $this->invoices = Cache::remember('draft_invoices', now()->addMinutes(15), function () {
            return Invoice::where('status', InvoiceStatus::DRAFT)
                ->with('client')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($invoice) => [
                    'uuid' => $invoice->uuid,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client?->abbreviation ?? $invoice->client?->name ?? 'Unknown',
                    'total' => $invoice->total,
                    'created_at' => $invoice->created_at->format('M j'),
                ])
                ->toArray();
        });
    }

    #[On('invoice-created')]
    public function clearCache(): void
    {
        Cache::forget('draft_invoices');
        $this->loadInvoices();
    }

    public function refresh(): void
    {
        Cache::forget('draft_invoices');
        $this->loadInvoices();
    }
};
?>

<div class="flex h-full flex-col p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Draft Invoices</h3>
        <button wire:click="refresh" wire:loading.attr="disabled" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <flux:icon.arrow-path class="size-4" wire:loading.class="animate-spin" />
        </button>
    </div>

    @if(empty($invoices))
        <div class="flex flex-1 items-center justify-center">
            <p class="text-sm text-gray-500">No draft invoices</p>
        </div>
    @else
        <div class="flex-1 overflow-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/50">
                            <td class="py-2 pr-2">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $invoice['invoice_number'] }}</div>
                                <div class="text-xs text-gray-500">{{ $invoice['client_name'] }}</div>
                            </td>
                            <td class="py-2 pr-2 text-right">
                                <div class="font-medium text-gray-900 dark:text-white">${{ number_format($invoice['total'], 0) }}</div>
                                <div class="text-xs text-gray-500">{{ $invoice['created_at'] }}</div>
                            </td>
                            <td class="py-2 text-right">
                                <a href="{{ route('invoices.edit', $invoice['uuid']) }}" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Cached for 15 min</p>
</div>