<?php

use App\Models\Client;
use App\Models\Contact;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Flux\Flux;

new class extends Component {
    public ?int $clientId = null;
    public ?Client $client = null;

    public ?int $contactId = null;
    public ?int $newPrimaryId = null;

    #[On('remove-client-contact')]
    public function open(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->client = Client::findOrFail($clientId);
        $this->contactId = null;
        $this->newPrimaryId = null;

        $this->resetValidation();
        Flux::modal('remove-contact')->show();
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

    #[Computed]
    public function selectedContact(): ?Contact
    {
        if (!$this->contactId) {
            return null;
        }

        return Contact::find($this->contactId);
    }

    #[Computed]
    public function isRemovingPrimary(): bool
    {
        if (!$this->contactId || !$this->client) {
            return false;
        }

        return $this->client->contacts()
            ->wherePivot('is_primary', true)
            ->where('contacts.id', $this->contactId)
            ->exists();
    }

    #[Computed]
    public function otherContacts()
    {
        if (!$this->client || !$this->contactId) {
            return collect();
        }

        return $this->client->contacts()
            ->where('contacts.id', '!=', $this->contactId)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function needsNewPrimarySelection(): bool
    {
        return $this->isRemovingPrimary && $this->otherContacts->count() > 1;
    }

    public function removeContact(): void
    {
        if (!$this->contactId) {
            return;
        }

        $otherContacts = $this->otherContacts;

        // Detach the selected contact
        $this->client->contacts()->detach($this->contactId);

        // Handle primary contact assignment
        if ($otherContacts->count() === 1) {
            // Only one remaining - make them primary
            $this->client->contacts()->updateExistingPivot(
                $otherContacts->first()->id,
                ['is_primary' => true]
            );
        } elseif ($otherContacts->count() > 1 && $this->isRemovingPrimary) {
            // Multiple remaining and removing primary - use selected new primary
            if ($this->newPrimaryId) {
                // Clear any existing primary
                $this->client->contacts()->wherePivot('is_primary', true)
                    ->each(fn ($contact) => $this->client->contacts()->updateExistingPivot(
                        $contact->id,
                        ['is_primary' => false]
                    ));

                // Set new primary
                $this->client->contacts()->updateExistingPivot(
                    $this->newPrimaryId,
                    ['is_primary' => true]
                );
            }
        }

        $this->reset(['clientId', 'client', 'contactId', 'newPrimaryId']);
        Flux::modal('remove-contact')->close();
        $this->dispatch('client-updated');
    }

    #[Computed]
    public function canSubmit(): bool
    {
        if (!$this->contactId) {
            return false;
        }

        if ($this->needsNewPrimarySelection && !$this->newPrimaryId) {
            return false;
        }

        return true;
    }
};
?>

<div>
    <flux:modal name="remove-contact" class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">Remove Contact from {{ $client?->name }}</flux:heading>

            @if($this->clientContacts->isEmpty())
                <p class="text-zinc-500">This client has no contacts.</p>
            @else
                <flux:select label="Select Contact to Remove" wire:model.live="contactId" placeholder="Choose a contact...">
                    @foreach($this->clientContacts as $contact)
                        <flux:select.option value="{{ $contact->id }}">
                            {{ $contact->full_name }}
                            @if($contact->pivot->is_primary) (Primary) @endif
                        </flux:select.option>
                    @endforeach
                </flux:select>

                @if($this->needsNewPrimarySelection)
                    <flux:radio.group wire:model.live="newPrimaryId" label="Select New Primary Contact">
                        @foreach($this->otherContacts as $contact)
                            <flux:radio value="{{ $contact->id }}" label="{{ $contact->full_name }}" />
                        @endforeach
                    </flux:radio.group>
                @endif

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button
                        type="button"
                        variant="danger"
                        wire:click="removeContact"
                        :disabled="!$this->canSubmit"
                    >
                        Remove Contact
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:modal>
</div>