<?php

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    #[Validate('required|string|max:255|unique:clients,name')]
    public string $name = '';

    #[Validate('required|string|max:10|unique:clients,abbreviation')]
    public string $abbreviation = '';

    #[Validate('nullable|date|after_or_equal:today')]
    public ?string $audition_date = null;

    public function save(): void
    {
        $this->validate();

        Client::create([
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'audition_date' => $this->audition_date ?: null,
        ]);

        $this->reset();
        Flux::modal('create-client')->close();
        $this->dispatch('client-created');
    }
};
?>

<div>
    <flux:modal.trigger name="create-client">
        <flux:button icon="plus" variant="primary">
            New Client
        </flux:button>
    </flux:modal.trigger>


    <flux:modal name="create-client" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Client</flux:heading>

            <flux:input label="Name" wire:model="name" />
            <flux:input label="Abbreviation" wire:model="abbreviation" maxlength="10" />
            <flux:input label="Audition Date" wire:model="audition_date" type="date" />

            <div class="flex gap-2">
                <flux:spacer />
{{--                <flux:button variant="ghost" wire:click="$flux.modal('create-client').close()">Cancel</flux:button>--}}
                <flux:button type="submit" variant="primary">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
