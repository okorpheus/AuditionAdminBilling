<?php

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Flux\Flux;

new class extends Component {
    public ?int $clientId = null;
    public ?Client $client = null;
    public ?int $primaryId = null;

    #[On('set-primary-contact')]
    public function open(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->client = Client::findOrFail($clientId);
        $this->primaryId = $this->client->contacts()
            ->wherePivot('is_primary', true)
            ->first()?->id;

        Flux::modal('set-primary-contact')->show();
    }

    #[Computed]
    public function clientContacts()
    {
        if (!$this->client) {
            return collect();
        }

        return $this->client->contacts()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function save(): void
    {
        if (!$this->primaryId) {
            return;
        }

        // Clear existing primary
        $this->client->contacts()->wherePivot('is_primary', true)
            ->each(fn ($contact) => $this->client->contacts()->updateExistingPivot(
                $contact->id,
                ['is_primary' => false]
            ));

        // Set new primary
        $this->client->contacts()->updateExistingPivot(
            $this->primaryId,
            ['is_primary' => true]
        );

        $this->reset(['clientId', 'client', 'primaryId']);
        Flux::modal('set-primary-contact')->close();
        $this->dispatch('client-updated');
    }
};
?>

<div>
    <flux:modal name="set-primary-contact" class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">Set Primary Contact for {{ $client?->name }}</flux:heading>

            <flux:radio.group wire:model="primaryId" label="Select Primary Contact">
                @foreach($this->clientContacts as $contact)
                    <flux:radio value="{{ $contact->id }}" label="{{ $contact->full_name }}" />
                @endforeach
            </flux:radio.group>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="button" variant="primary" wire:click="save">
                    Save
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>