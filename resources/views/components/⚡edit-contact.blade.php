<?php

use App\Models\Contact;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Flux\Flux;

new class extends Component {
    public ?int $contactId = null;

    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[On('edit-contact')]
    public function edit(int $contactId): void
    {
        $this->contactId = $contactId;
        $contact = Contact::findOrFail($contactId);

        $this->first_name = $contact->first_name;
        $this->last_name = $contact->last_name;
        $this->email = $contact->email;
        $this->phone = $contact->phone ?? '';

        $this->resetValidation();
        Flux::modal('edit-contact')->show();
    }

    public function save(): void
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:contacts,email,' . $this->contactId,
            'phone' => 'nullable|string|max:20',
        ]);

        $contact = Contact::findOrFail($this->contactId);
        $contact->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
        ]);

        $this->reset();
        Flux::modal('edit-contact')->close();
        $this->dispatch('contact-updated');
    }
};
?>

<div>
    <flux:modal name="edit-contact" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Edit Contact</flux:heading>

            <flux:input label="First Name" wire:model="first_name" />
            <flux:input label="Last Name" wire:model="last_name" />
            <flux:input label="Email" wire:model="email" type="email" />
            <flux:input label="Phone" wire:model="phone" type="tel" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>
</div>