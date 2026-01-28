<?php

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Flux\Flux;

new class extends Component {
    public ?int $clientId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|max:10')]
    public string $abbreviation = '';

    #[Validate('nullable|date|after_or_equal:today')]
    public ?string $audition_date = null;

    #[On('edit-client')]
    public function edit(int $clientId): void
    {
        $this->clientId = $clientId;
        $client = Client::findOrFail($clientId);

        $this->name = $client->name;
        $this->abbreviation = $client->abbreviation;
        $this->audition_date = $client->audition_date?->format('Y-m-d');

        $this->resetValidation();
        Flux::modal('edit-client')->show();
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:clients,name,' . $this->clientId,
            'abbreviation' => 'required|string|max:10|unique:clients,abbreviation,' . $this->clientId,
            'audition_date' => 'nullable|date|after_or_equal:today',
        ]);

        $client = Client::findOrFail($this->clientId);
        $client->update([
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'audition_date' => $this->audition_date ?: null,
        ]);

        $this->reset();
        Flux::modal('edit-client')->close();
        $this->dispatch('client-updated');
    }
};
?>

<div>
    <flux:modal name="edit-client" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Edit Client</flux:heading>

            <flux:input label="Name" wire:model="name" />
            <flux:input label="Abbreviation" wire:model="abbreviation" maxlength="10" />
            <flux:input label="Audition Date" wire:model="audition_date" type="date" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>
</div>