<?php

use App\Models\Client;
use App\Models\Contact;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Flux\Flux;

new class extends Component {
    public ?int $clientId = null;
    public ?Client $client = null;

    public ?int $contactId = null;
    public bool $isPrimary = false;

    // For creating new contact
    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|email|max:255|unique:contacts,email')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    public bool $newContactIsPrimary = false;

    #[On('add-client-contact')]
    public function open(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->client = Client::findOrFail($clientId);
        $this->contactId = null;
        $this->isPrimary = !$this->client->contacts()->exists();

        $this->resetValidation();
        Flux::modal('add-contact')->show();
    }

    #[Computed]
    public function availableContacts()
    {
        if (!$this->client) {
            return collect();
        }

        $existingContactIds = $this->client->contacts()->pluck('contacts.id');

        return Contact::whereNotIn('id', $existingContactIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function openCreateModal(): void
    {
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone = '';
        $this->newContactIsPrimary = !$this->client->contacts()->exists();

        $this->resetValidation();
        Flux::modal('add-contact')->close();
        Flux::modal('create-client-contact')->show();
    }

    public function backToSelect(): void
    {
        Flux::modal('create-client-contact')->close();
        Flux::modal('add-contact')->show();
    }

    public function attachContact(): void
    {
        if (!$this->contactId) {
            return;
        }

        if ($this->isPrimary) {
            $this->client->contacts()->updateExistingPivot(
                $this->client->contacts()->wherePivot('is_primary', true)->pluck('contacts.id'),
                ['is_primary' => false]
            );
        }

        $this->client->contacts()->attach($this->contactId, ['is_primary' => $this->isPrimary]);

        $this->reset(['clientId', 'client', 'contactId', 'isPrimary']);
        Flux::modal('add-contact')->close();
        $this->dispatch('client-updated');
    }

    public function createAndAttach(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:contacts,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $contact = Contact::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
        ]);

        if ($this->newContactIsPrimary) {
            $this->client->contacts()->updateExistingPivot(
                $this->client->contacts()->wherePivot('is_primary', true)->pluck('contacts.id'),
                ['is_primary' => false]
            );
        }

        $this->client->contacts()->attach($contact->id, ['is_primary' => $this->newContactIsPrimary]);

        $this->reset(['clientId', 'client', 'contactId', 'isPrimary', 'first_name', 'last_name', 'email', 'phone', 'newContactIsPrimary']);
        Flux::modal('create-client-contact')->close();
        $this->dispatch('client-updated');
        $this->dispatch('contact-created');
    }

    #[Computed]
    public function clientHasContacts(): bool
    {
        return $this->client?->contacts()->exists() ?? false;
    }
};
?>

<div>
    <flux:modal name="add-contact" class="md:w-96">
        <div class="space-y-6">
            <flux:heading size="lg">Add Contact to {{ $client?->name }}</flux:heading>

            <flux:select label="Select Contact" wire:model.live="contactId" placeholder="Choose a contact...">
                @foreach($this->availableContacts as $contact)
                    <flux:select.option value="{{ $contact->id }}">
                        {{ $contact->full_name }} ({{ $contact->email }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:button variant="subtle" wire:click="openCreateModal" icon="plus" class="w-full">
                Create New Contact
            </flux:button>

            @if($this->clientHasContacts)
                <flux:checkbox wire:model="isPrimary" label="Set as primary contact" />
            @endif

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="button" variant="primary" wire:click="attachContact" :disabled="!$contactId">
                    Add Contact
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="create-client-contact" class="md:w-96">
        <form wire:submit="createAndAttach" class="space-y-6">
            <flux:heading size="lg">Create Contact for {{ $client?->name }}</flux:heading>

            <flux:input label="First Name" wire:model="first_name" />
            <flux:input label="Last Name" wire:model="last_name" />
            <flux:input label="Email" wire:model="email" type="email" />
            <flux:input label="Phone" wire:model="phone" type="tel" />

            @if($this->clientHasContacts)
                <flux:checkbox wire:model="newContactIsPrimary" label="Set as primary contact" />
            @endif

            <div class="flex gap-2">
                <flux:button type="button" variant="ghost" wire:click="backToSelect">Back</flux:button>
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create & Add</flux:button>
            </div>
        </form>
    </flux:modal>
</div>