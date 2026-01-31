<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

new class extends Component {
    public ?array $invoices = null;
    public float $totalOpen = 0;

    public function mount(): void
    {
        $this->loadInvoices();
    }

    public function loadInvoices(): void
    {
        $data = Cache::remember('open_invoices', now()->addMinutes(15), function () {
            $allOpen = Invoice::where('status', InvoiceStatus::POSTED)->with('client')->get();

            return [
                'total' => $allOpen->sum('balance_due'),
                'invoices' => $allOpen
                    ->sortBy('invoice_date')
                    ->take(5)
                    ->map(fn($invoice) => [
                        'uuid' => $invoice->uuid,
                        'invoice_number' => $invoice->invoice_number,
                        'client_name' => $invoice->client?->abbreviation ?? $invoice->client?->name ?? 'Unknown',
                        'invoice_date' => $invoice->invoice_date?->format('M j, Y'),
                        'days_old' => $invoice->invoice_date?->diffInDays(now()),
                        'balance_due' => $invoice->balance_due,
                    ])
                    ->values()
                    ->toArray(),
            ];
        });

        // Handle stale cache format
        if (!isset($data['total'])) {
            Cache::forget('open_invoices');
            $this->loadInvoices();
            return;
        }

        $this->totalOpen = $data['total'];
        $this->invoices = $data['invoices'];
    }

    public function refresh(): void
    {
        Cache::forget('open_invoices');
        $this->loadInvoices();
    }
};
?>

<div class="flex h-full flex-col p-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Invoices</h3>
        <button wire:click="refresh" wire:loading.attr="disabled" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <flux:icon.arrow-path class="size-4" wire:loading.class="animate-spin" />
        </button>
    </div>
    <p class="text-xl font-semibold text-gray-900 dark:text-white mb-1">${{ number_format($totalOpen, 0) }}</p>

    @if(empty($invoices))
        <div class="flex flex-1 items-center justify-center">
            <p class="text-sm text-gray-500">No open invoices</p>
        </div>
    @else
        <div class="flex-1 overflow-auto -mx-1">
            <table class="w-full text-xs">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/50">
                            <td class="py-1 px-1">
                                <a href="{{ route('invoices.edit', $invoice['uuid']) }}" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ $invoice['invoice_number'] }}
                                </a>
                            </td>
                            <td class="py-1 px-1 text-gray-600 dark:text-gray-300 truncate max-w-[80px]" title="{{ $invoice['client_name'] }}">
                                {{ $invoice['client_name'] }}
                            </td>
                            <td class="py-1 px-1 text-right font-medium text-gray-900 dark:text-white">
                                ${{ number_format($invoice['balance_due'], 0) }}
                            </td>
                            <td class="py-1 px-1 text-right text-gray-500">
                                {{ $invoice['days_old'] }}d
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Cached for 15 min</p>
</div>