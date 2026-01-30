<?php

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Stripe\Stripe;
use Stripe\Balance;

new class extends Component {
    public ?array $balance = null;
    public ?string $error = null;

    public function mount(): void
    {
        $this->loadBalance();
    }

    public function loadBalance(): void
    {
        try {
            $this->balance = Cache::remember('stripe_balance', now()->addMinutes(15), function () {
                Stripe::setApiKey(config('services.stripe.secret'));
                $balance = Balance::retrieve();

                return [
                    'available' => collect($balance->available)->map(fn($b) => [
                        'amount' => $b->amount / 100,
                        'currency' => strtoupper($b->currency),
                    ])->toArray(),
                    'pending' => collect($balance->pending)->map(fn($b) => [
                        'amount' => $b->amount / 100,
                        'currency' => strtoupper($b->currency),
                    ])->toArray(),
                ];
            });
            $this->error = null;
        } catch (\Exception $e) {
            $this->error = 'Unable to fetch Stripe balance';
            $this->balance = null;
        }
    }

    public function refresh(): void
    {
        Cache::forget('stripe_balance');
        $this->loadBalance();
    }
};
?>

<div class="flex h-full flex-col justify-between p-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Stripe Balance</h3>
        <button wire:click="refresh" wire:loading.attr="disabled" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <flux:icon.arrow-path class="size-4" wire:loading.class="animate-spin" />
        </button>
    </div>

    @if($error)
        <div class="flex flex-1 items-center justify-center">
            <p class="text-sm text-red-500">{{ $error }}</p>
        </div>
    @elseif($balance)
        <div class="flex flex-1 flex-col justify-center space-y-3">
            @foreach($balance['available'] as $available)
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Available</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        ${{ number_format($available['amount'], 2) }}
                        <span class="text-sm font-normal text-gray-500">{{ $available['currency'] }}</span>
                    </p>
                </div>
            @endforeach
            @foreach($balance['pending'] as $pending)
                @if($pending['amount'] > 0)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-lg font-medium text-gray-600 dark:text-gray-300">
                            ${{ number_format($pending['amount'], 2) }}
                            <span class="text-sm font-normal text-gray-500">{{ $pending['currency'] }}</span>
                        </p>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="flex flex-1 items-center justify-center">
            <flux:icon.arrow-path class="size-6 animate-spin text-gray-400" />
        </div>
    @endif

    <p class="text-xs text-gray-400 dark:text-gray-500">Cached for 15 min</p>
</div>