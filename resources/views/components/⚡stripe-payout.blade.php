<?php

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Stripe\Stripe;
use Stripe\Payout;

new class extends Component {
    public ?array $payout = null;
    public ?string $error = null;

    public function mount(): void
    {
        $this->loadPayout();
    }

    public function loadPayout(): void
    {
        try {
            $this->payout = Cache::remember('stripe_latest_payout', now()->addMinutes(15), function () {
                Stripe::setApiKey(config('services.stripe.secret'));
                $payouts = Payout::all(['limit' => 1]);

                if (empty($payouts->data)) {
                    return null;
                }

                $payout = $payouts->data[0];

                return [
                    'amount' => $payout->amount / 100,
                    'currency' => strtoupper($payout->currency),
                    'status' => $payout->status,
                    'arrival_date' => $payout->arrival_date,
                    'created' => $payout->created,
                ];
            });
            $this->error = null;
        } catch (\Exception $e) {
            $this->error = 'Unable to fetch payout';
            $this->payout = null;
        }
    }

    public function refresh(): void
    {
        Cache::forget('stripe_latest_payout');
        $this->loadPayout();
    }

    public function statusColor(): string
    {
        return match($this->payout['status'] ?? '') {
            'paid' => 'green',
            'pending' => 'yellow',
            'in_transit' => 'blue',
            'canceled', 'failed' => 'red',
            default => 'gray',
        };
    }
};
?>

<div class="flex h-full flex-col justify-between p-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Latest Payout</h3>
        <button wire:click="refresh" wire:loading.attr="disabled" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <flux:icon.arrow-path class="size-4" wire:loading.class="animate-spin" />
        </button>
    </div>

    @if($error)
        <div class="flex flex-1 items-center justify-center">
            <p class="text-sm text-red-500">{{ $error }}</p>
        </div>
    @elseif($payout)
        <div class="flex flex-1 flex-col justify-center space-y-2">
            <div>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    ${{ number_format($payout['amount'], 2) }}
                    <span class="text-sm font-normal text-gray-500">{{ $payout['currency'] }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge :color="$this->statusColor()">{{ ucfirst($payout['status']) }}</flux:badge>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($payout['status'] === 'paid')
                    Arrived {{ \Carbon\Carbon::createFromTimestamp($payout['arrival_date'])->format('M j, Y') }}
                @else
                    Expected {{ \Carbon\Carbon::createFromTimestamp($payout['arrival_date'])->format('M j, Y') }}
                @endif
            </p>
        </div>
    @elseif($payout === null && !$error)
        <div class="flex flex-1 items-center justify-center">
            <p class="text-sm text-gray-500">No payouts yet</p>
        </div>
    @else
        <div class="flex flex-1 items-center justify-center">
            <flux:icon.arrow-path class="size-6 animate-spin text-gray-400" />
        </div>
    @endif

    <p class="text-xs text-gray-400 dark:text-gray-500">Cached for 15 min</p>
</div>