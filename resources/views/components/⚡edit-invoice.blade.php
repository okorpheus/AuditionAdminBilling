<?php

use Livewire\Component;

new class extends Component
{
    public $invoice;

    public function mount($invoice = null): void
    {
        $this->invoice = $invoice;
    }
};
?>

<div>
    <flux:heading size="xl">Edit Invoice</flux:heading>
    <flux:card>
        <flux:heading size="lg">Identifying Information</flux:heading>
    </flux:card>
</div>
